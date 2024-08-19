#!/bin/bash

# Define the file paths
CONFIG_FILE="path/to/config/file"
INCLUDE_FILE="path/to/include/file"
URL="https://tataplayjiotv.github.io/Jass/jass.mpd"

# Read current startNumber from the configuration file
startNumber=$(grep -oP '(?<=startNumber=")[0-9]+' "$CONFIG_FILE")

# Increment the startNumber
newNumber=$((startNumber + 1))

# Update the config file with the new startNumber
sed -i "s/startNumber=\"$startNumber\"/startNumber=\"$newNumber\"/" "$CONFIG_FILE"

# Download the file from the URL
curl -o "jass.mpd" "$URL"

# Include and process additional content
# Assuming the content of 'include_file.txt' is part of the script logic
echo "Processing additional content from $INCLUDE_FILE:"
while IFS= read -r line; do
    echo "Included line: $line"
done < "$INCLUDE_FILE"

echo "Updated startNumber to $newNumber"

# Optionally process the downloaded file
echo "Processing content from the downloaded file jass.mpd:"
while IFS= read -r line; do
    echo "Downloaded line: $line"
done < "jass.mpd"
