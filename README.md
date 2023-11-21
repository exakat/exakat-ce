# Exakat

The Exakat Engine is an automated code reviewing engine for PHP. 

## Installation

### Quick installation

Copy-paste the following code in your terminal. This was tested on OSX and Linux-debian. 

```bash
mkdir exakat
cd exakat
curl -O -J 'https://www.exakat.io/versions/index.php?file=latest'
    
curl -O -J 'https://www.exakat.io/versions/index.php?file=latest.md5'
md5sum -c exakat-*.md5
// Example : 
// exakat-2.6.0.phar: OK

mv exakat-*.phar exakat.phar

php exakat.phar install -v 

php exakat.phar doctor
```

### Installation with docker

The docker usage is detailled in the [installation documentation](https://exakat.readthedocs.io/en/latest/Gettingstarted/Docker.html).

```bash
docker pull exakat/exakat:latest
```

### Saas Services

Exakat has a enterprise version, available online at [exakat.io](http://www.exakat.io/). 


### Run online

Projects smaller than 10k lines of code may be [tested online](http://www.exakat.io/free-trial/), with the most recent version of exakat. 

## Contribute

See [CONTRIBUTING.md](https://github.com/exakat/exakat/blob/master/CONTRIBUTING.md) for information on how to contribute to the Exakat engine.

## Changelog

See [Changelog.txt](https://github.com/exakat/exakat/blob/master/ChangeLog.txt) for information on how to contribute to the Exakat engine.

