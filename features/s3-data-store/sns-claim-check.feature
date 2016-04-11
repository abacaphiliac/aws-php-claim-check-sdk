# language: en
@s3 @sns @sqs
Feature: SNS+SQS+S3 Claim Check

  Background:
    Given a data store named "php-sdk-claim-check"
    And a topic named "ClaimCheckTopic"
    And a queue named "ClaimCheckQueue"
    And a queue named "ClaimCheckQueue" is subscribed to a topic named "ClaimCheckTopic"
    And a queue named "AnotherClaimCheckQueue"
    And a queue named "AnotherClaimCheckQueue" is subscribed to a topic named "ClaimCheckTopic"

  Scenario: A large message uses the claim check pattern
    Given I have a large message
    When I send the message to a topic named "ClaimCheckTopic"
    Then I can fetch the message from a queue named "ClaimCheckQueue"
    And I can fetch the message from a queue named "AnotherClaimCheckQueue"
