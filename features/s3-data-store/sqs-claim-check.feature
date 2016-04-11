# language: en
@s3 @sqs
Feature: SQS+S3 Claim Check
  
  Background:
    Given a data store named "php-sdk-claim-check"
    And a queue named "ClaimCheckQueue"

  Scenario: A large message uses the claim check pattern
    Given I have a large message
    When I send the message to a queue named "ClaimCheckQueue"
    Then I can fetch the message from a queue named "ClaimCheckQueue"
