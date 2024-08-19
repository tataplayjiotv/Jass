import xml.etree.ElementTree as ET
from datetime import datetime

# Load the MPD file
tree = ET.parse('jass.mpd')
root = tree.getroot()

# Define the namespace dictionary
namespaces = {
    '': 'urn:mpeg:dash:schema:mpd:2011',
    'cenc': 'urn:mpe:cenc:2013'
}

# Find the SegmentTemplate elements
for adaptation_set in root.findall('.//AdaptationSet', namespaces):
    for representation in adaptation_set.findall('Representation', namespaces):
        segment_template = representation.find('SegmentTemplate', namespaces)
        if segment_template is not None:
            # Update the startNumber with a new value (example: increment based on current time)
            new_start_number = str(int(datetime.now().timestamp()))
            segment_template.set('startNumber', new_start_number)

# Save the updated MPD file
tree.write('jass.mpd')
