
import networkx as nx
import math

G = nx.read_edgelist("EdgeList.txt", create_using=nx.DiGraph())

pagerank = nx.pagerank(G, alpha=0.85, personalization=None, max_iter=30, tol=1e-06, nstart=None, weight='weight', dangling=None)

with open("external_pageRankFile.txt", "w", encoding="utf-8") as f:
    for pageid in pagerank:
        f.write("/Users/pratiksha/solr-7.7.2/crawl_data/" + pageid + "=" + str(math.log(pagerank[pageid])) + "\n")