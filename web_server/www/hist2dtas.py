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
x,y = zip(*data)

mylen = len(x)

x = np.asarray(x)
y = np.asarray(y)

fig = plt.figure()

plt.hist2d(x, y, bins=(300,150), norm=LogNorm())

figtext(0.005, 0.005, `mylen`+' values plotted.', alpha=.6, fontsize=8)

figtext(0.005, 0.020, 'EarthChem Portal', alpha=.6, fontsize=8)

"""
Draw lines for TAS
"""
plt.plot([41,41,52.5],[0,7,14], linewidth=1, color='black',path_effects=[path_effects.Stroke(linewidth=3, foreground='white'),path_effects.Normal()])
plt.plot([50,52.5,57.6,63,63],[15.1,14,11.7,7,0], linewidth=1, color='black',path_effects=[path_effects.Stroke(linewidth=3, foreground='white'),path_effects.Normal()])
plt.plot([76.3,69],[0,8], linewidth=1, color='black',path_effects=[path_effects.Stroke(linewidth=3, foreground='white'),path_effects.Normal()])
plt.plot([36,46],[10,10], linewidth=1, color='black',path_effects=[path_effects.Stroke(linewidth=3, foreground='white'),path_effects.Normal()])
plt.plot([36,41],[7,7], linewidth=1, color='black',path_effects=[path_effects.Stroke(linewidth=3, foreground='white'),path_effects.Normal()])
plt.plot([36,45],[3,3], linewidth=1, color='black',path_effects=[path_effects.Stroke(linewidth=3, foreground='white'),path_effects.Normal()])
plt.plot([45,45,45,49.4,53,57.6,61],[0,3,5,7.3,9.3,11.7,13.5], linewidth=1, color='black',path_effects=[path_effects.Stroke(linewidth=3, foreground='white'),path_effects.Normal()])
plt.plot([45,52,69,69],[5,5,8,13], linewidth=1, color='black',path_effects=[path_effects.Stroke(linewidth=3, foreground='white'),path_effects.Normal()])
plt.plot([45,49.4,52,52],[9.4,7.3,5,0], linewidth=1, color='black',path_effects=[path_effects.Stroke(linewidth=3, foreground='white'),path_effects.Normal()])
plt.plot([48.4,53,57,57],[11.5,9.3,5.9,0], linewidth=1, color='black',path_effects=[path_effects.Stroke(linewidth=3, foreground='white'),path_effects.Normal()])

ax = fig.add_subplot(111)

ax.set_xlim([36, 84])
ax.set_ylim([0, 18])

ax.set_xlabel('SiO2 (wt. percent)')
ax.set_ylabel('Na2O + K2O (wt. percent)')

"""
Put names on graph
"""
text = ax.text(37.25,8.75, 'Foidite', fontsize=10, alpha=1, color='black'); text.set_path_effects([path_effects.Stroke(linewidth=3, foreground='white'),path_effects.Normal()])
text = ax.text(54.70,15, 'Phonolite', fontsize=10, alpha=1, color='black'); text.set_path_effects([path_effects.Stroke(linewidth=3, foreground='white'),path_effects.Normal()])
text = ax.text(49.5,11.5, 'Tephriphonolite', fontsize=10, alpha=1, color='black'); text.set_path_effects([path_effects.Stroke(linewidth=3, foreground='white'),path_effects.Normal()])
text = ax.text(45.75,9.3, 'Phonotephrite', fontsize=10, alpha=1, color='black'); text.set_path_effects([path_effects.Stroke(linewidth=3, foreground='white'),path_effects.Normal()])
text = ax.text(42,6.75, 'Tephrite', fontsize=10, alpha=1, color='black'); text.set_path_effects([path_effects.Stroke(linewidth=3, foreground='white'),path_effects.Normal()])
text = ax.text(41.5,5.75, 'Basanite', fontsize=10, alpha=1, color='black'); text.set_path_effects([path_effects.Stroke(linewidth=3, foreground='white'),path_effects.Normal()])
text = ax.text(41.5,2.45, 'Picro-basalt', fontsize=10, alpha=1, color='black'); text.set_path_effects([path_effects.Stroke(linewidth=3, foreground='white'),path_effects.Normal()])
text = ax.text(62.5,12.75, 'Trachyte', fontsize=10, alpha=1, color='black'); text.set_path_effects([path_effects.Stroke(linewidth=3, foreground='white'),path_effects.Normal()])
text = ax.text(62.5,9.5, 'Trachydacite', fontsize=10, alpha=1, color='black'); text.set_path_effects([path_effects.Stroke(linewidth=3, foreground='white'),path_effects.Normal()])
text = ax.text(54,9, 'Trachyandesite', fontsize=10, alpha=1, color='black'); text.set_path_effects([path_effects.Stroke(linewidth=3, foreground='white'),path_effects.Normal()])
text = ax.text(46.7,5.4, 'Trachybasalt', fontsize=10, alpha=1, color='black'); text.set_path_effects([path_effects.Stroke(linewidth=3, foreground='white'),path_effects.Normal()])
text = ax.text(51,6.5, 'Basaltic\nTrachy-\nandesite', fontsize=10, alpha=1, color='black'); text.set_path_effects([path_effects.Stroke(linewidth=3, foreground='white'),path_effects.Normal()])
text = ax.text(47.5,3.55, 'Basalt', fontsize=10, alpha=1, color='black'); text.set_path_effects([path_effects.Stroke(linewidth=3, foreground='white'),path_effects.Normal()])
text = ax.text(72.,8.25, 'Rhyolite', fontsize=10, alpha=1, color='black'); text.set_path_effects([path_effects.Stroke(linewidth=3, foreground='white'),path_effects.Normal()])
text = ax.text(65.75,3.55, 'Dacite', fontsize=10, alpha=1, color='black'); text.set_path_effects([path_effects.Stroke(linewidth=3, foreground='white'),path_effects.Normal()])
text = ax.text(58,3.55, 'Andesite', fontsize=10, alpha=1, color='black'); text.set_path_effects([path_effects.Stroke(linewidth=3, foreground='white'),path_effects.Normal()])
text = ax.text(52.5,3.75, 'Basaltic\nAndesite', fontsize=10, alpha=1, color='black'); text.set_path_effects([path_effects.Stroke(linewidth=3, foreground='white'),path_effects.Normal()])

ax.arrow( 38, 8, 0.0, -3, fc="black", ec="black", head_width=0.25, head_length=0.3, alpha=1, path_effects=[path_effects.Stroke(linewidth=3, foreground='white'),path_effects.Normal()] )
ax.arrow( 38.5, 9.5, 4.1, 3, fc="black", ec="black", head_width=0.25, head_length=0.3, alpha=1, path_effects=[path_effects.Stroke(linewidth=3, foreground='white'),path_effects.Normal()] )

ax.set_aspect(1.4)

plt.colorbar(fraction=0.02525, pad=0.01)

plt.grid(True)

plt.xticks(np.arange(40, 84, 5.0))

plt.title('Total Alkali vs SiO2', fontsize=20)

fig.tight_layout()

fig.set_size_inches(14.5, 7.5)

fig.savefig('hist2dplots/tas_'+pkey+'.png', dpi=100)

