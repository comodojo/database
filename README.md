# comodojo/database

[![Build Status](https://api.travis-ci.org/comodojo/database.png)](http://travis-ci.org/comodojo/database) [![Latest Stable Version](https://poser.pugx.org/comodojo/database/v/stable)](https://packagist.org/packages/comodojo/database) [![Total Downloads](https://poser.pugx.org/comodojo/database/downloads)](https://packagist.org/packages/comodojo/database) [![Latest Unstable Version](https://poser.pugx.org/comodojo/database/v/unstable)](https://packagist.org/packages/comodojo/database) [![License](https://poser.pugx.org/comodojo/database/license)](https://packagist.org/packages/comodojo/database) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/comodojo/database/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/comodojo/database/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/comodojo/database/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/comodojo/database/?branch=master)

Multi-database library with query builder

## Introduction
 
This library provides a standardized layer to connect and send queries to different kind of databases.

Currently it supports:

- MySQL 4.1 and above via **mysqli**
- MySQL 3.x, 4.x and 5.x databases using **PDO_MYSQL**
- Oracle through the OCI library and **PDO_OCI**
- SQLite 3 using **PDO_OCI**
- PostgreSQL through **pgsql** extension
- Microsoft SQL Server and Sybase using **PDO_DBLIB**
- IBM DB2 Universal Database, IBM Cloudscape, and Apache Derby through **ibm_db2** extension

It integrates a query builder (still in development) that helps creation of queries across different databases.

**Documentation of this library is not yet available!**

*Pre-release code (unsupported) is still available [here](https://github.com/comodojo/database/releases/tag/0.1.0).*

## Installation

Install [composer](https://getcomposer.org/), then:

`` composer require comodojo/database 1.0.* ``
