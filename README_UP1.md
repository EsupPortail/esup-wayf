A cron could be created "0 5 * * * cd /webhome/wayf/www;bash ./Geo-SWITCHwayf/update.sh renater &>> /var/log/wayf/wayf.log"

Those logs are saved into wayf.log

The script update.sh will update IDPS and SPS from downloaded metadata and via 'readmetadata.php' it will
create 2 new files into the current folder the following files 'IDProvider.metadata.php', 'SProvider.metadata.php'.

So that means the script 'update.sh' needs to be executed into the folder 'www/' so the index page file WAYF(php file) can access the 2 created files.
