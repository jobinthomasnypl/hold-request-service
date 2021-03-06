# NYPL Hold Request Service

[![Build Status](https://travis-ci.org/NYPL/hold-request-service.svg?branch=master)](https://travis-ci.org/NYPL/hold-request-service)
[![Coverage Status](https://coveralls.io/repos/github/NYPL/hold-request-service/badge.svg?branch=master)](https://coveralls.io/github/NYPL/hold-request-service?branch=master)

This package is intended to be used as a Lambda-based Hold Request Service using the [NYPL PHP Microservice Starter](https://github.com/NYPL/php-microservice-starter).

This package adheres to [PSR-1](http://www.php-fig.org/psr/psr-1/), [PSR-2](http://www.php-fig.org/psr/psr-2/), and [PSR-4](http://www.php-fig.org/psr/psr-4/) (using the [Composer](https://getcomposer.org/) autoloader).

## Service Responsibilities

The Hold Request Service receives a request from the Discovery interface
and processes the intended action. A request can be for a hold in Sierra
or ReCAP.

Once the service validates the request, saves it to its database instance,
and sends a new request to its Kinesis stream and returns a successful
response or returns an error response.

After these responsibilities are met, the contract ends and another
service takes over or the request terminates in a response from this
service.

The Kinesis stream can be consumed in order to further process a hold
request via a Sierra API service or ReCAP API service. These services
will further process the hold request and make modifications, if necessary.
These downstream services can update with information garnered from the
APIs mentioned. These APIs can modify the Hold Request by changing its
status.

A Hold Request is governed by the following Avro 1.8.1 schema:

## Schema
~~~
{
  "patron": "string",
  "requestType": "string",
  "nyplSource": "string",
  "recordType": "string",
  "record": "string",
  "pickupLocation": "string",
  "neededBy": "string",
  "numberOfCopies": "int",
  "docDeliveryData": {
    "chapterTitle": "string",
    "emailAddress": "string",
    "startPage": "string",
    "endPage": "string",
    "issue": "string",
    "volume": "string"
  }
}
~~~

## Hold Request Data

* recordType - (b, i, j)
* record - identifier [bibId (b), itemId (i), volumeId (j)]
* pickupLocation - NYPL location identifier
* neededBy - date when hold is terminated (business rules?)
* numberOfCopies - should always be 1 for ReCAP

## ReCAP API RequestItem
(https://uat-recap.htcinc.com:9093/swagger-ui.html#/)

## ReCAP Request Data

* All require requestType, itemBarcodes, patronBarcode, trackingId, bibId,
itemOwningInstitution, requestingInstitution
* Retrieval/Recall related: author, callNumber, deliveryLocation
* Temp record related: titleIdentifier
* EDD requires email
* EDD related: startPage, endPage, volume, issue, chapterTitle

## Requirements

* Node.js >=6.0
* PHP >=7.0 
  * [pdo_pdgsql](http://php.net/manual/en/ref.pdo-pgsql.php)

Homebrew is highly recommended for PHP:
  * `brew install php71`
  * `brew install php71-pdo-pgsql`
  
## Installation

1. Clone the repo.
2. Install required dependencies.
   * Run `npm install` to install Node.js packages.
   * Run `composer install` to install PHP packages.
   * If you have not already installed `node-lambda` as a global package, run `npm install -g node-lambda`.
3. Setup [configuration](#configuration) files.
   * Copy the `.env.sample` file to `.env`.
   * Copy `config/var_qa.env.sample` to `config/var_qa.env` and `config/var_production.env.sample` to `config/var_production.env`.

## Security

Authorization provided via OAuth2 authorization_code. Set scopes in the format of access_type:service.
For example, read:holds to access the GET request method endpoints.

## Configuration

Various files are used to configure and deploy the Lambda.

### .env

`.env` is used *locally* for two purposes:

1. By `node-lambda` for deploying to and configuring Lambda in *all* environments. 
   * You should use this file to configure the common settings for the Lambda 
   (e.g. timeout, role, etc.) and include AWS credentials to deploy the Lambda. 
2. To set local environment variables so the Lambda can be run and tested in a local environment.
   These parameters are ultimately set by the [var environment files](#var_environment) when the Lambda is deployed.

### package.json

Configures `npm run` deployment commands for each environment and sets the proper AWS Lambda VPC and
security group.
 
~~~~
"scripts": {
  "deploy-qa": "node-lambda deploy -e qa -f config/deploy_qa.env -S config/event_sources_qa.json -b subnet-f4fe56af -g sg-1d544067",
  "deploy-production": "node-lambda deploy -e production -f config/deploy_production.env -S config/event_sources_production.json -b subnet-f4fe56af -g sg-1d544067"
},
~~~~

### var_app

Configures environment variables common to *all* environments.

### var_*environment*

Configures environment variables specific to each environment.

### event_sources_*environment*

Configures Lambda event sources (triggers) specific to each environment.

## Usage

### Process a Lambda Event

To use `node-lambda` to process the sample API Gateway event in `event.json`, run:

~~~~
node-lambda run
~~~~

### Run as a Web Server

To use the PHP internal web server, run:

~~~~
php -S localhost:8888 -t . index.php
~~~~

You can then make a request to the Lambda: `http://localhost:8888/api/v0.1/hold-requests`.

### Swagger Documentation Generator

Create a Swagger route to generate Swagger specification documentation:

~~~~
$service->get("/docs", SwaggerGenerator::class);
~~~~
