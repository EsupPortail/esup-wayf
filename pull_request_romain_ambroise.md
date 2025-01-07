# For pull request


## Introduction 

Bonjour,
proposition de pull-request qui nous a permis d'intégrer votre module dans nos applis open-source et notamment en SaaS.

Liste des changements : 
- modification du script xmlcombine.py
-> on a rencontré plusieurs problèmes avec la version précédente dont des fichiers xml invalides et le merge entre deux fichiers

- modification du script update.sh

Il faut savoir que l'on met à jour quotidiennement les metadata pour nos shibboleth-sp 
Ainsi, on a remarqué qu'on avait un 'crash' tout les 10 jours environ sur un xml malformé récupéré depuis Renater.
Proposition : 

- intégration d'un système pour vérifier si :
    * tout est ok lors du chargement des metadata de shibboleth
    * => sinon on reste sur les données de la veille


By Romain AMBROISE Université de Caen, Normandie
contact : romain.ambroise@unicaen.fr

## Dépendances

apt-get install -y curl

### Php for wayf
RUN apt-get install -y php7.4-fpm php7.4-common php7.4-xml php7.4-mbstring libapache2-mod-php

### Librairies pour le module geoswitchwayf
apt-get install -y php-curl
apt-get install -y wget
apt-get install -y gzip
RUN apt-get install -y imagemagick
RUN apt-get install -y python3 python3-pip
RUN pip3 install lxml
RUN apt-get install -y libxml2-utils
RUN apt-get install -y unzip
RUN apt-get install -y wget

## Integration of switchwayf
RUN mkdir -p /var/www/html/switchwayf
COPY ./deployment-configuration/shibboleth/switchwayf /var/www/html/switchwayf
RUN chmod -R go=u-w /var/www/html/switchwayf

## Autres
### Configuration Shibboleth

RUN mkdir -p /var/cache/shibboleth
RUN chown -R www-data:www-data /var/cache/shibboleth
RUN chmod -R 755 /var/cache/shibboleth

RUN a2enmod shib

# End