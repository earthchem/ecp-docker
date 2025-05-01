import json
import sys
import psycopg2
import matplotlib
matplotlib.use('AGG')

import matplotlib.patheffects as path_effects
from matplotlib.colors import LogNorm
import numpy as np
import matplotlib.pyplot as plt

# Load database config
with open('/dbconfig/config.inc.python.json') as json_data_file:
    dbconfig = json.load(json_data_file)

connstring = (
    f"dbname={dbconfig['pg_connection']['db']} "
    f"user={dbconfig['pg_connection']['user']} "
    f"password={dbconfig['pg_connection']['passwd']} "
    f"host={dbconfig['pg_connection']['host']}"
)

itemlist = ['al2o3', 'mgo', 'tio2', 'na2o', 'cao', 'k2o', 'p2o5', 'feot']

if len(sys.argv) < 2:
    print('Invalid number of arguments.')
    sys.exit()

pkey = sys.argv[1]

# Connect to database and fetch TAS query string
conn = psycopg2.connect(connstring)
cur = conn.cursor()

query = f"SELECT earthchemwheretext FROM search_query WHERE pkey={pkey}"
cur.execute(query)
data = cur.fetchone()
cur.close()
conn.close()

query = data[0] if data else None

# Connect to database again to fetch data
conn = psycopg2.connect(connstring)
cur = conn.cursor()

cur.execute(query)
data = cur.fetchall()
cur.close()
conn.close()

# Unpack data
url, sample_pkey, sio2, al2o3, mgo, tio2, na2o, cao, k2o, p2o5, feot = zip(*data)

mylen = len(sio2)

# Convert to numpy arrays
sio2 = np.asarray(sio2)
al2o3 = np.asarray(al2o3)
mgo = np.asarray(mgo)
tio2 = np.asarray(tio2)
na2o = np.asarray(na2o)
cao = np.asarray(cao)
k2o = np.asarray(k2o)
p2o5 = np.asarray(p2o5)
feot = np.asarray(feot)

# Generate plots
for i in itemlist:
    fig, ax = plt.subplots()

    y = locals()[i]  # Dynamically get corresponding variable
    mylabel = i.upper()

    plt.hist2d(sio2, y, bins=(100, 100), norm=LogNorm())

    fig.text(0.005, 0.005, f"{mylen} values plotted.", alpha=.6, fontsize=8)
    fig.text(0.005, 0.020, 'EarthChem Portal', alpha=.6, fontsize=8)

    ax.set_xlabel('SiO2')
    ax.set_ylabel(mylabel)
    plt.colorbar()
    plt.grid(True)
    plt.title(f"{mylabel} vs SiO2", fontsize=20)

    fig.savefig(f'hist2dplots/harker_{i}_{pkey}.png', dpi=100)