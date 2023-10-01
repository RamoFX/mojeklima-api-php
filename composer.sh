#!/bin/bash

docker run --rm --interactive --tty -v $PWD:/app composer "$@"
