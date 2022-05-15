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

```
cp .env.example .env and update needed fields
```

## Usage

### Enter bash shell
```
 docker exec -it php-apache2 bash
```

### List available commands
```
bin/console list
```
```
root@415787353ed2:/var/www/html# bin/console list

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
bin/console app:xml-data-importer
```
```
root@415787353ed2:/var/www/html# bin/console app:xml-data-importer -h
Description:
  XML data importer to (CSV,JSON,Google Sheet or SQlite)

Usage:
  app:xml-data-importer [options]
  app:xml-data-importer

Options:
      --fetch=FETCH     fetch XML from local or server? [default: "server"]
      --to=TO           want to store the data? (SQlite, Google Spreadsheet, JSON file etc). [default: "JSON"]
  -h, --help            Display help for the given command. When no command is given display help for the list command
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi|--no-ansi  Force (or disable --no-ansi) ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Help:
  This command allows you to Import a XML file into (CSV, JSON, Google Sheet & SQlite)
```

Import XML
===================
Import data from XML to CSV

    bin/console app:xml-data-importer 
    
Import data from XML to JSON

    bin/console app:xml-data-importer --fetch=server --to=JSON

Import data from XML to GoogleSheet

    bin/console app:xml-data-importer --fetch=server --to=GoogleSheet
    
Information
===================
Log file store in log/import-xml.log
Save google sheet credentials in config/google folder and mention the path and name in .env file

