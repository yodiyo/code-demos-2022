#!/bin/bash

# Build script for custom theme and plugins
# Add or remove plugins as needed if need to do one at a time
# May add option to clean and build or update

function cleanandbuild() {
    pwd
    echo "* Removing node and vendor modules"
    rm -vrf node_modules vendor
    echo "* Building..."
    npm start
}

THEMES=(
    '[ADD THEME HERE]'
)

PLUGINS=(
    '[ADD PLUGIN(S) HERE]'
)

for THEMENAME in "${THEMES[@]}"
do
    cd wp-content/themes/$THEMENAME
    echo
    echo "*******************"
    echo
    echo "Let's build the $THEMENAME theme"
    echo
    cleanandbuild
    echo
    echo "* $THEMENAME BUILD COMPLETE *"
    echo
    echo "*******************"
    echo
    cd ../../../
done

for PLUGINNAME in "${PLUGINS[@]}"
do
    cd wp-content/plugins/$PLUGINNAME
    echo
    echo "*******************"
    echo
    echo "Let's build the $PLUGINNAME plugin"
    echo
    cleanandbuild
    echo
    echo "* $PLUGINNAME BUILD COMPLETE *"
    echo
    echo "*******************"
    echo
    cd ../../../
done
