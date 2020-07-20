<?php
ini_set('memory_limit','-1');
include 'SpellCorrector.php';
include 'simple_html_dom.php';
header('Content-Type: text/html; charset=utf-8');

$div=false;
$correct = "";
$correct1="";
$output = "";
$array = array_map('str_getcsv', file('/Users/pratiksha/Downloads/solr-7.7.2/URLtoHTML_latimes_news.csv'));
$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$rankingAlgo = isset($_GET['algo']) ? $_GET['algo'] : false;
$results = false;
$solr_params = array(
  'fl' => 'title,og_url,og_description,id'
);
$pagerank_params = array(
  'fl' => 'title,og_url,og_description,id',
  'sort' => 'pageRank desc'
);

if ($query)
{
  require_once('Apache/Solr/Service.php');
  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample/');
  if (get_magic_quotes_gpc() == 1)
  {
    $query = stripslashes($query);
  }
  $correct = SpellCorrector::correct($query);
  try{
    if ($rankingAlgo == "solr") {
      $results = $solr->search($query, 0, $limit, $solr_params);
    } 
    else {
      $results = $solr->search($query, 0, $limit, $pagerank_params);
    }
  }
  catch (Exception $e)
  {
    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
  }
  $total = (int) $results->response->numFound;
  if($total==0){
    $div = true;
    $link = "http://localhost:8080/index.php?q=$correct";
    $output = "Show results for this instead: <a href='$link'>$correct</a>";
  }
}
?>
<html>

  <head>
  <style>
input[type=text], select {
  width: 20%;
  padding: 2px 2px;
  margin: 8px 0;
  display: inline-block;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
}
input[type=submit] {
  

  color: black;
  padding: 4px 14px;
  margin: 8px 0;
  border: 1px solid;
  border-radius: 4px;
  cursor: pointer;
}
</style>
    <title>Search Engine</title>
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="http://code.jquery.com/jquery-1.10.2.js"></script>
    <script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
  </head>
  <body>
    <form accept-charset="utf-8" method="get">
          <label for="q">Search:</label>

      <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>" list="searchresults"  autocomplete="off"/>
      <br><br>
       
      <input id="radio1" type="radio" name="algo" <?php if($rankingAlgo != "pagerank") { echo "checked='checked'"; } ?> value="solr"> Lucene 
      <input id="radio2" type="radio" name="algo" <?php if($rankingAlgo == "pagerank") { echo "checked='checked'"; } ?> value="pagerank"> External Page Rank
      <br>
      <input type="submit" value="Submit"/>
    </form>
  <script>
   $(function() {
     var URL_PREFIX = "http://localhost:8983/solr/myexample/suggest?q=";
     var URL_SUFFIX = "&wt=json&indent=true";
     var count=0;
     var tags = [];
     $("#q").autocomplete({
       source : function(request, response) {
         var correct="",before="";
         var query = $("#q").val().toLowerCase();
         var character_count = query.length - (query.match(/ /g) || []).length;
         var querym = query.replace(/ /g,"_");
         var URL = URL_PREFIX + querym+ URL_SUFFIX;
        $.ajax({
         url : URL,
         success : function(data) {
          var js =data.suggest.suggest;
          var docs = JSON.stringify(js);
          var jsonData = JSON.parse(docs);
          var result =jsonData[querym].suggestions;
          var j=0;
          var stem =[];
          var tags = []
          for(var i=0;i<5;i++){
            tags[i] = result[i].term
          }
          console.log(tags);
          response(tags);
        },
        dataType : 'jsonp',
        jsonp : 'json.wrf'
      });
      },
      minLength : 1
    })
   });
 </script>
<?php
if ($div){
  echo $output;
}
if ($results)
{
  $total = (int) $results->response->numFound;
  $start = min(1, $total);
  $end = min($limit, $total);
?>
  <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
    <ol>
<?php
  foreach ($results->response->docs as $doc)
  {
    $title = $doc->title;
    $url = $doc->og_url;
    $id = $doc->id;
    $description = $doc->og_description;
?>
  
  <li>
 <table style="border: 1px solid black; text-align: left">
  <tr>
 <th>TITLE</th>
 <td><a href="<?php echo $url ?>"> <?php  if (isset($title)) {
        echo htmlspecialchars($title, ENT_NOQUOTES, 'utf-8');
      } else {
        echo "NA";
      } ?> </a></td>
</tr>
<tr>
 <th>URL</th>
 <td><a href="<?php echo $url ?>">  <?php if (isset($url)) {
        echo htmlspecialchars($url, ENT_NOQUOTES, 'utf-8');
      } else {
        foreach($array as $row){
          if($id =='/Users/pratiksha/Downloads/solr-7.7.2/crawl_data/'.$row[0]){
              $url = $row[1];
            }
        }
        echo $url;
      } ?>  </a></td>
</tr>
<tr>
 <th>ID</th>
 <td><?php  if (isset($id)) {
        echo htmlspecialchars($id, ENT_NOQUOTES, 'utf-8');
      } else {
        echo "NA";
      } ?></td>
</tr>
<tr>
 <th>DESCRIPTION</th>
 <td><?php if (isset($description)) {
        echo htmlspecialchars($description, ENT_NOQUOTES, 'utf-8');
      } else {
        echo "NA";
      } ?></td>
 </tr>
</table>
 </li>
<?php
 }

}
?>
 </body>
</html>