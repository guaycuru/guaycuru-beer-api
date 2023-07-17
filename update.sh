#!/usr/bin/env bash

git pull
bin/doctrine orm:clear-cache:metadata
bin/doctrine orm:clear-cache:query
bin/doctrine orm:schema-tool:update --dump-sql --force
bin/doctrine orm:generate-proxies
