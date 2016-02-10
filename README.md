# The Symfony Demo Application on Roids!
[![Build Status](https://travis-ci.org/alfonsomga/symfony.demo.on.roids.svg)](https://travis-ci.org/alfonsomga/symfony.demo.on.roids)[![Total Downloads](https://poser.pugx.org/alfonsomga/symfony.demo.on.roids/downloads)](https://packagist.org/packages/alfonsomga/symfony.demo.on.roids)[![License](https://poser.pugx.org/alfonsomga/symfony.demo.on.roids/license)](https://packagist.org/packages/alfonsomga/symfony.demo.on.roids)

The "Symfony Demo Application on Roids" is an application based on the original [**Symfony Demo Application**](https://github.com/symfony/symfony-demo) that includes extra features using technologies like [**Elasticsearch**](https://www.elastic.co/products/elasticsearch), [**OAuth**](http://oauth.net/), [**RabbitMQ**](https://www.rabbitmq.com/), [**Redis**](http://redis.io/) and a [**RESTful API**](https://en.wikipedia.org/wiki/Representational_state_transfer) + [**HATEOAS**](https://en.wikipedia.org/wiki/HATEOAS).

<p align="center">
  <a href="#"><img src="http://svgporn.com/logos/elasticsearch.svg" heigh="15%" width="15%"></a>
  <a href="#"><img src="http://svgporn.com/logos/oauth.svg" heigh="15%" width="15%"></a>
  <a href="#"><img src="http://svgporn.com/logos/rabbitmq.svg" heigh="15%" width="15%"></a>
  <a href="#"><img src="http://svgporn.com/logos/redis.svg" heigh="15%" width="15%"></a>
  <a href="#"><img src="https://i.imgur.com/qovozc2.png" heigh="15%" width="15%"></a>
</p>

## Table of Contents
- [Setting up & running the demo with Vagrant + Ansible](#setting-up--running-the-demo-with-vagrant--ansible)
  - [Prerequisites](#prerequisites)
  - [Instructions](#instructions)
- [RESTful API + HATEOAS](#restful-api--hateoas)
- [Elasticsearch](#elasticsearch)
- [OAuth](#oauth)
- [RabbitMQ](#rabbitmq)
- [Redis](#redis)

<img src="http://svgporn.com/logos/ansible.svg" heigh="5%" width="5%" align="right">
<img src="http://svgporn.com/logos/vagrant.svg" heigh="5%" width="5%" align="right">
## Setting up & running the demo with [**Vagrant**](https://www.vagrantup.com/) + [**Ansible**](http://www.ansible.com/)
<img src="http://fotos.subefotos.com/dd1a2c7b983291b6bba45185952f1eaeo.png">
### Prerequisites
- [Vagrant](https://www.vagrantup.com/downloads.html) installed
- [Virtualbox](https://www.virtualbox.org/wiki/Downloads) installed

### Instructions
1. ``git clone https://github.com/alfonsomga/symfony.demo.on.roids.git``
2. ``cd symfony.demo.on.roids/vagrant/``
3. ``vagrant up``
4. Wait until Ansible installs and configure everything
5. Finally navigate to <a href="http://192.168.50.88/" target="_blank">**http://192.168.50.88**</a> to browse the app
 
Congratulations! You're now ready to use The Symfony Demo On Roids.

<img src="https://i.imgur.com/qovozc2.png" heigh="10%" width="10%" align="right">
## RESTful API + HATEOAS
<img src="http://fotos.subefotos.com/902ef9199023b7d7ff1e37aadc32ee09o.png">

A RESTful API + HATEOAS has been implemented for expose the resources. Different formats are available for manage the data: HTML, JSON and XML.

**Related urls:**
- <a href="http://192.168.50.88/api/v1/" target="_blank">**API Index page**</a>
- <a href="http://192.168.50.88/api/doc" target="_blank">**API Documentation**</a>

**Bundles used:**
- [**FOSRestBundle**](https://github.com/FriendsOfSymfony/FOSRestBundle)
- [**JMSSerializerBundle**](https://github.com/schmittjoh/JMSSerializerBundle)
- [**NelmioApiDocBundle**](https://github.com/nelmio/NelmioApiDocBundle)
- [**FOSHttpCacheBundle**](https://github.com/FriendsOfSymfony/FOSHttpCacheBundle)
- [**BazingaHateoasBundle**](https://github.com/willdurand/BazingaHateoasBundle)
- [**BazingaRestExtraBundle**](https://github.com/willdurand/BazingaRestExtraBundle)

<img src="http://svgporn.com/logos/elasticsearch.svg" heigh="10%" width="10%" align="right">
## Elasticsearch
<img src="http://fotos.subefotos.com/073c48b4ad7243e1ca4385dc34f5a2e9o.png">

Elasticsearch has been used for add a simple search form and show relevant results based on the user search query.

**Related urls:**
- <a href="http://192.168.50.88:9200/_plugin/head/" target="_blank">**Elasticsearch Admin panel**</a>
- <a href="http://192.168.50.88/blog/search-results?q=Lorem+ipsum" target="_blank">**Elasticsearch app search page (results for ``Lorem ipsum``)**</a>

**Bundles used:**
- [**FOSElasticaBundle**](https://github.com/FriendsOfSymfony/FOSElasticaBundle)

<img src="http://svgporn.com/logos/oauth.svg" heigh="15%" width="10%" align="right">
## OAuth
<img src="http://fotos.subefotos.com/8aa0e2f21490393c399ed412b0003ba3o.png">

OAuth has been used for link/unlink an account from an OAuth provider (GitHub in this case) to an existent backend account and allows to log in directly to the backend from a GitHub account.

**Related urls:**
- <a href="http://192.168.50.88/en/login" target="_blank">**Login page (click on ``Sign in with GitHub``)**</a>
- <a href="http://192.168.50.88/en/admin/post/" target="_blank">**Manage your OAuth account (``Link`` or ``Unlink`` ``Github Account``)**</a>

**Bundles used:**
- [**HWIOAuthBundle**](https://github.com/hwi/HWIOAuthBundle)

<img src="http://svgporn.com/logos/rabbitmq.svg" heigh="10%" width="10%" align="right">
## RabbitMQ
<img src="http://fotos.subefotos.com/39b1eaf4c05ef3124701805f9d3a80d7o.png">

RabbitMQ has been used for generate a PDF file based on the article content from a consumer in a scalable way.

**Related urls:**
- <a href="http://192.168.50.88:15672" target="_blank">**RabbitMQ Admin panel (User: ``admin`` password: ``symfony.demo.on.roids``)**</a>
- <a href="http://192.168.50.88/en/blog/posts/lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit" target="_blank">**Post page (click on ``Download post as PDF``)**</a>

**Bundles used:**
- [**RabbitMqBundle**](https://github.com/videlalvaro/RabbitMqBundle)
- [**KnpSnappyBundle**](https://github.com/KnpLabs/KnpSnappyBundle)

<img src="http://svgporn.com/logos/redis.svg" heigh="10%" width="10%" align="right">
## Redis
<img src="http://fotos.subefotos.com/8e6e6a3507f8145c3bf72d3c9af53951o.png">

Redis has been used for show the top 5 popular posts and set a cache lifetime of 3600 seconds.

**Related urls:**
- <a href="http://192.168.50.88/blog/top-5-popular-posts" target="_blank">**Top 5 popular posts**</a>

**Bundles used:**
- [**SncRedisBundle**](https://github.com/snc/SncRedisBundle)
