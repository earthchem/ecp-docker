import json
import sys

with open('/dbconfig/config.inc.python.json') as json_data_file:
    dbconfig = json.load(json_data_file)

connstring = "dbname="+dbconfig['pg_connection']['db']+" user="+dbconfig['pg_connection']['user']+" password="+dbconfig['pg_connection']['passwd']+" host="+dbconfig['pg_connection']['host']


import psycopg2

import matplotlib
matplotlib.use('AGG')

import matplotlib.patheffects as path_effects

from matplotlib.colors import LogNorm
from pylab import *

itemlist = ['al2o3','mgo','tio2','na2o','cao','k2o','p2o5','feot'];

if len(sys.argv)<2:
	print 'Invalid number of arguments.'
	sys.exit()

pkey = sys.argv[1]

# Connect to database
conn = psycopg2.connect(connstring)

# Open a cursor to perform database operations
cur = conn.cursor()

# fetch the TAS query string
query = "select earthchemwheretext from search_query where pkey="+pkey

# execute the query
cur.execute(query)

data = cur.fetchall()
cur.close()
conn.close()

data = data[0]
query = data[0]

# Connect to database
conn = psycopg2.connect(connstring)

# Open a cursor to perform database operations
cur = conn.cursor()

# execute the query
cur.execute(query)

# retrieve the whole result set
data = cur.fetchall()
cur.close()
conn.close()

#unpack data
url,sample_pkey,sio2,al2o3,mgo,tio2,na2o,cao,k2o,p2o5,feot = zip(*data)

mylen = len(sio2)

sio2 = np.asarray(sio2)
al2o3 = np.asarray(al2o3)
mgo = np.asarray(mgo)
tio2 = np.asarray(tio2)
na2o = np.asarray(na2o)
cao = np.asarray(cao)
k2o = np.asarray(k2o)
p2o5 = np.asarray(p2o5)
feot = np.asarray(feot)

"""
sio2
al2o3
mgo
tio2
na2o
cao
k2o
p2o5
feot


SiO2
Al2O3
MgO
TiO2
Na2O
CaO
K2O
P2O5
FeOt
"""

for i in itemlist:

	fig = plt.figure()

	if i == 'al2o3': y = al2o3; mylabel = 'Al2O3';
	if i == 'mgo': y = mgo; mylabel = 'MgO';
	if i == 'tio2': y = tio2; mylabel = 'TiO2';
	if i == 'na2o': y = na2o; mylabel = 'Na2O';
	if i == 'cao': y = cao; mylabel = 'CaO';
	if i == 'k2o': y = k2o; mylabel = 'K2O';
	if i == 'p2o5': y = p2o5; mylabel = 'P2O5';
	if i == 'feot': y = feot; mylabel = 'FeOt';

	
	plt.hist2d(sio2, y, bins=(100,100), norm=LogNorm())

	figtext(0.005, 0.005, `mylen`+' values plotted.', alpha=.6, fontsize=8)

	figtext(0.005, 0.020, 'EarthChem Portal', alpha=.6, fontsize=8)


	ax = fig.add_subplot(111)

	#ax.set_xlim([36, 84])
	#ax.set_ylim([0, 18])

	ax.set_xlabel('SiO2')
	ax.set_ylabel(mylabel)



	#ax.set_aspect(1.4)

	#plt.colorbar(fraction=0.02525, pad=0.01)

	plt.colorbar()

	plt.grid(True)

	#plt.xticks(np.arange(40, 84, 5.0))

	plt.title(mylabel+' vs SiO2', fontsize=20)

	#fig.tight_layout()

	#fig.set_size_inches(4, 3)

	fig.savefig('hist2dplots/harker_'+i+'_'+pkey+'.png', dpi=100)

