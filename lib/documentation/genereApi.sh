#!/bin/sh
basedir=$(dirname $(pwd))
#rm -r output
phpdoc -d ../ --ignore=documentation/*,plugins/*

