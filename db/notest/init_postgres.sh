#!/bin/sh

# Create the database user. You will be queried for a password.
# This is the password that must be set in the connect string.
createuser -A -d -P ebm
# Create the database.
# When prompted for the password, use the one you gave out
# before. You'll be prompted twice.
createdb --username=ebm -W ebm EasyBookMarks
