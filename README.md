# Exakat

The Exakat Engine is an automated code reviewing engine for PHP. 

## Installation

### Quick installation

Copy-paste the following code in your terminal. This was tester on OSX and Linux-debian. 

```bash
mkdir exakat
cd exakat
curl -o exakat.phar https://www.exakat.io/versions/index.php?file=latest
curl -o apache-tinkerpop-gremlin-server-3.4.12-bin.zip https://www.exakat.io/versions/apache-tinkerpop-gremlin-server-3.4.12-bin.zip
unzip apache-tinkerpop-gremlin-server-3.4.12-bin.zip
mv apache-tinkerpop-gremlin-server-3.4.12 tinkergraph
rm -rf apache-tinkerpop-gremlin-server-3.4.12-bin.zip

# Optional : install neo4j engine.
cd tinkergraph
./bin/gremlin-server.sh install org.apache.tinkerpop neo4j-gremlin 3.4.12
cd ..

php exakat.phar doctor
```

### Installation with the phar

Phar is the recommended installation process.

The Exakat engine is [distributed as a phar archive](https://www.exakat.io/download-exakat/). Phar contains all the needed PHP code to run it. 

The rest of the installation (Gremlin-server) is detailled in the [installation documentation](https://exakat.readthedocs.io/en/latest/Installation.html).

The quick installation guide is the following (command line, MacOS. See docs for more options): 

```bash
mkdir exakat
cd exakat
curl -o exakat.phar https://www.exakat.io/versions/index.php?file=latest
curl -o apache-tinkerpop-gremlin-server-3.4.12-bin.zip https://www.exakat.io/versions/apache-tinkerpop-gremlin-server-3.4.12-bin.zip
unzip apache-tinkerpop-gremlin-server-3.4.12-bin.zip
mv apache-tinkerpop-gremlin-server-3.4.12 tinkergraph
rm -rf apache-tinkerpop-gremlin-server-3.4.12-bin.zip

# Optional : install neo4j engine.
cd tinkergraph
./bin/gremlin-server.sh install org.apache.tinkerpop neo4j-gremlin 3.4.12
cd ..

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

