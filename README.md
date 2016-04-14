[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/abacaphiliac/aws-sdk-php-claim-check/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/abacaphiliac/aws-sdk-php-claim-check/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/abacaphiliac/aws-sdk-php-claim-check/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/abacaphiliac/aws-sdk-php-claim-check/?branch=master)
[![Build Status](https://travis-ci.org/abacaphiliac/aws-sdk-php-claim-check.svg?branch=master)](https://travis-ci.org/abacaphiliac/aws-sdk-php-claim-check)

# abacaphiliac/aws-php-claim-check-sdk

An implementation of the 
[Claim Check](http://www.enterpriseintegrationpatterns.com/patterns/messaging/StoreInLibrary.html)
Enterprise Integration Pattern, utilizing Amazon Web Services. AWS recommends usage of the Claim Check pattern on the
[SQS Compliance FAQ](https://aws.amazon.com/sqs/faqs/#Compliance).

![StoreInLibrary](http://www.enterpriseintegrationpatterns.com/img/StoreInLibrary.gif "StoreInLibrary")

This library provides extended SNS and SQS clients to "Check Luggage", uses S3 as the "Data Store", 
and uses the extended SQS client as the "Data Enricher". 

This package aims to be a compatible port of
Amazon's [Extended Client Library](https://github.com/awslabs/amazon-sqs-java-extended-client-lib).
Messages stored via this PHP package should be able to be received by the Java package.

# AWS [Extended Client Library](https://github.com/awslabs/amazon-sqs-java-extended-client-lib) Compatibility
## Similarities
* Claim Check structure is identical, i.e. keys are `s3BucketName` and `s3Key`, 
meaning you can publish to SQS+S3 via this PHP lib and read via the AWS Extended Client Library java lib.

## Differences
* Messages published from SNS to SQS contain a nested Claim Check message structure, 
so the Java SDK is not be able to natively consume messages published to SNS (citation needed).
* Usage of the pattern is not configurable by message size, nor can the pattern be disabled in this lib.
Use the wrapped clients if you do not want to use Claim Check.
* The AWS Extended Client Library will always delete the message from S3 when the message is deleted from SQS.
This is not acceptable when messages are published to SNS and fanned-out to multiple subscribers
(e.g. multiple SQS queues). This package allows you to disable deletion from S3 in the SQS extended client 
configuration.

# Installation
```
composer require abacaphiliac/aws-php-claim-check-sdk
```

## Contributing
```
composer install && vendor/bin/phing
```

This library attempts to comply with [PSR-1][], [PSR-2][], and [PSR-4][]. If
you notice compliance oversights, please send a patch via pull request.

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md

# Tasks
- [x] Add SQS check-in.
- [x] Add SQS check-out.
- [x] Add SNS check-in.
- [ ] Use WireMock to stub API responses and add feature tests to CI.
- [ ] Add async SQS check-in.
- [ ] Add async SQS check-out.
- [ ] Add async SNS check-in.
