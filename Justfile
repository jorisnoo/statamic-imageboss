#!/usr/bin/env just --justfile

test:
    vendor/bin/pest

lint:
    vendor/bin/pint
