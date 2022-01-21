php exakat.ce.phar version

php exakat.ce.phar remove -p sculpin-tu -v

php exakat.ce.phar init -p sculpin-tu -R https://github.com/sculpin/sculpin.git -v

php exakat.ce.phar project -p sculpin-tu -v

php exakat.ce.phar report -p sculpin-tu -v --format Text -T Security
php exakat.ce.phar report -p sculpin-tu -v --format Perfile -T Security
php exakat.ce.phar report -p sculpin-tu -v --format Sarif -T Security
php exakat.ce.phar report -p sculpin-tu -v --format Phpcompilation 

php exakat.ce.phar catalog 

php exakat.ce.phar catalog  -json

