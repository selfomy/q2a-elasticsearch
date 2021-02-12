# q2a-elasticsearch
Improve Q2A search feature by using ElasticSearch - the top powerful, fast and scalable full-text search engine.
Originally made by [Vijay Sharma](https://github.com/vijsha79/q2a-elasticsearch)

## Features
- According to our benchmark, we experienced an improvement in response time when searching from 35 seconds to 5 seconds (70% faster).
- All features of the default search module of Q2A. 
- Powered by ElasticSearch - a powerful, fast and scalable full-text search engine. 

## Requirements
- ElasticSearch server (self-hosted or on-premise), read Install section to install ES server if you don't have one. 
- Q2A minimun version: 1.8

## Install
1. Install ElasticSearch on your server, [read more](https://www.elastic.co/guide/en/elasticsearch/reference/current/install-elasticsearch.html)
2. Clone or download this plugin and save it to qa-plugin of your Q2A site
3. Navigate to the q2a-elasticsearch/es-client folder
4. Run ```composer install```, in case you're using shared hosting, please run it at your local computer and then upload to shared host. You'll need to install composer first.
3. Setup ElasticSearch connection parameters in Q2A admin, leave username and password field empty if you don't create any ES user. 
4. Reindex all content by going to Admin>Stats

## ElasticSearch query
```
{
    "query": {
        "multi_match": {
            "query": "hàm số 4x^3 - 3x^2 + 2x-2",
            "fields": ["title", "content", "text"]
        }
    },
    "collapse": {
        "field": "questionid"
    },
    "from": 0,
    "size": 5
}
```

## Contributions
Feel free to open a pull request or an issue that you are facing and we'll try to help.
Selfomy is an educational company that has a product based on Q2A. Therefore, we're working with Q2A code almost every day so you can expect this repo to be very active.

Selfomy - [Website](https://selfomy.com) | [Facebook](https://facebook.com/selfomy) | [Github](https://github.com/selfomy) | [Linkedin](https://www.linkedin.com/company/selfomy)
