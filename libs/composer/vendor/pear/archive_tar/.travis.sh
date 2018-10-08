#!/bin/bash

composer validate
cd tests
pear run-tests
