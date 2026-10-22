#!/usr/bin/env bash
# [IT-Z] Interface entièrement en français (à lancer depuis le dossier site/)
ddev wp language core install fr_FR --activate
ddev wp language plugin install --all fr_FR
ddev wp language theme install --all fr_FR
