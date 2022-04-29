# XML-Importer
A template for Symfony console commands to be used within Docker containers

## Requirements
- Docker version 20.10.12, build e91ed57
- docker-compose version 1.29.2, build 5becea4c

## Clone & install packages

```
git clone https://github.com/netstackappzdev/xml-importer.git
```

```
cd xml-importer/
```

```
docker-compose up -d --build
```
## Examples

### Enter bash shell
```
 docker exec -it php-apache2 bash
```

### List available commands
```
php index.php list
```
```
root@415787353ed2:/var/www/html# php index.php list

Usage:
  command [options] [arguments]

Options:
  -h, --help            Display help for the given command. When no command is given display help for the list command
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi|--no-ansi  Force (or disable --no-ansi) ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Available commands:
  completion             Dump the shell completion script
  help                   Display help for a command
  list                   List commands
 app
  app:xml-data-importer  [app:xml-data-importer] XML data importer to (CSV,JSON,Google Sheet or SQlite)
```

```
php index.php app:xml-data-importer
```
```
root@415787353ed2:/var/www/html# php index.php app:xml-data-importer -h
Description:
  XML data importer to (CSV,JSON,Google Sheet or SQlite)

Usage:
  app:xml-data-importer [options]
  app:xml-data-importer

Options:
      --fetch=FETCH     fetch XML from local or server? [default: "local"]
      --to=TO           want to store the data? (SQlite, GoogleSheet, JSON file etc). [default: "JSON"]
  -h, --help            Display help for the given command. When no command is given display help for the list command
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi|--no-ansi  Force (or disable --no-ansi) ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Help:
  This command allows you to Import a XML file into (CSV, JSON, GoogleSheet & SQlite)
```