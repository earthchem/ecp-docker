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
x, y = zip(*data)

mylen = len(x)

# Convert to numpy arrays
x = np.asarray(x)
y = np.asarray(y)

# Create figure
fig, ax = plt.subplots()

plt.hist2d(x, y, bins=(300, 150), norm=LogNorm())

fig.text(0.005, 0.005, f"{mylen} values plotted.", alpha=.6, fontsize=8)
fig.text(0.005, 0.020, 'EarthChem Portal', alpha=.6, fontsize=8)

# Draw lines for TAS
lines = [
    ([41, 41, 52.5], [0, 7, 14]),
    ([50, 52.5, 57.6, 63, 63], [15.1, 14, 11.7, 7, 0]),
    ([76.3, 69], [0, 8]),
    ([36, 46], [10, 10]),
    ([36, 41], [7, 7]),
    ([36, 45], [3, 3]),
    ([45, 45, 45, 49.4, 53, 57.6, 61], [0, 3, 5, 7.3, 9.3, 11.7, 13.5]),
    ([45, 52, 69, 69], [5, 5, 8, 13])
]

for x_vals, y_vals in lines:
    plt.plot(x_vals, y_vals, linewidth=1, color='black', path_effects=[
        path_effects.Stroke(linewidth=3, foreground='white'), path_effects.Normal()
    ])

ax.set_xlim([36, 84])
ax.set_ylim([0, 18])
ax.set_xlabel('SiO2 (wt. percent)')
ax.set_ylabel('Na2O + K2O (wt. percent)')

# Place names on the graph
labels = {
    (37.25, 8.75): "Foidite",
    (54.70, 15): "Phonolite",
    (49.5, 11.5): "Tephriphonolite",
    (45.75, 9.3): "Phonotephrite",
    (42, 6.75): "Tephrite",
    (41.5, 5.75): "Basanite",
    (41.5, 2.45): "Picro-basalt",
    (62.5, 12.75): "Trachyte",
    (62.5, 9.5): "Trachydacite",
    (54, 9): "Trachyandesite"
}

for (x_pos, y_pos), label in labels.items():
    text = ax.text(x_pos, y_pos, label, fontsize=10, alpha=1, color='black')
    text.set_path_effects([
        path_effects.Stroke(linewidth=3, foreground='white'), path_effects.Normal()
    ])

ax.arrow(38, 8, 0.0, -3, fc="black", ec="black", head_width=0.25, head_length=0.3, alpha=1,
         path_effects=[path_effects.Stroke(linewidth=3, foreground='white'), path_effects.Normal()])
ax.arrow(38.5, 9.5, 4.1, 3, fc="black", ec="black", head_width=0.25, head_length=0.3, alpha=1,
         path_effects=[path_effects.Stroke(linewidth=3, foreground='white'), path_effects.Normal()])

ax.set_aspect(1.4)
plt.colorbar(fraction=0.02525, pad=0.01)
plt.grid(True)
plt.xticks(np.arange(40, 84, 5.0))
plt.title('Total Alkali vs SiO2', fontsize=20)

fig.tight_layout()
fig.set_size_inches(14.5, 7.5)
fig.savefig(f'hist2dplots/tas_{pkey}.png', dpi=100)
