#!/bin/bash

if grep -q 'ERROR' /var/log/shibboleth/shibd.log; then
    echo "Le service Shibboleth a un problème. Rechargement des données de la veille."
    rm /var/cache/shibboleth/metadata.xml
    cp /var/cache/shibboleth/metadata-yesterday.xml /var/cache/shibboleth/metadata.xml
    rm /var/log/shibboleth/shibd.log
    # Commande à mettre dans le cron : 
    # [PATH_TO_SCRIPT]/back-to-yesterday-metadata.sh && echo "Tout est OK. La mise à jour des metadata shibboleth est un succès." || ( echo "Erreur dans la mise à jour des metadata shibboleth. Rechargement des données de la veille." && /usr/bin/supervisorctl restart shibd )
    exit 1
else
    echo "Le service Shibboleth est opérationnel. OK"
    exit 0
fi
