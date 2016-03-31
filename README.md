# abacaphiliac/aws-php-claim-check-sdk

[Claim Check](http://www.enterpriseintegrationpatterns.com/patterns/messaging/StoreInLibrary.html) Enterprise Integration Pattern, 
implemented to use SQS[+SNS] for messaging with S3 as the data store.

TODO Link to AWS suggestion of SQS+S3 and java extended client sdk.

# compatibility with awslabs/amazon-sqs-java-extended-client-lib
## Similarities
* Claim Check structure is identical, e.g. keys are s3BucketName and s3Key, meaning you can publish to SQS+S3 via php and read via java.

## Differences
* SNS+SQS+S3 produced a nested Claim Check message structure, so the java sdk would not be able to natively consume messages published to SNS (really???).
* Usage of the pattern is not configurable by message size, nor able to be overridden... yet.
* Check-in and check-out interfaces use composition over inheritance. I'll try inheritance (like java client). 

# installation
composer require abacaphiliac/aws-php-claim-check-sdk

# contributing
```
vendor/bin/phpunit --coverage-text
```

# tasks
- [x] Add SQS check-in.
- [x] Add SQS check-out.
- [x] Add SNS check-in.
- [ ] "Check luggage" based on message size.
- [ ] Force "luggage check" by config, regardless of message size.
- [ ] Use WireMock to stub API responses and add feature tests to CI.
- [ ] Add async SQS check-in.
- [ ] Add async SQS check-out.
- [ ] Add async SNS check-in.
