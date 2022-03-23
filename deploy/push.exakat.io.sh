# Change the version
scp -i ~/.ssh/id_rsa_2021 exakat.ce.phar root@www.exakat.io:/var/www/versions/exakat-2.3.8.phar

# Version.php 
scp -i ~/.ssh/id_rsa_2021 versions.php root@www.exakat.io:/root/versions.php
scp -i ~/.ssh/id_rsa_2021 root@www.exakat.io:/root/versions.php versions.php 

ssh -i ~/.ssh/id_rsa_2021 root@www.exakat.io 'php versions.php'
ssh -i ~/.ssh/id_rsa_2021 root@www.exakat.io 'ls /var/www/versions/'
