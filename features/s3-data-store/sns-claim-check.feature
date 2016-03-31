# language: en
@sns @sqs @s3
Feature: SNS+SQS+S3 Claim Check

  Background:
    Given a data store named "sns-claim-check"
    And a queue named "SnsClaimCheckQueue"
    And a topic named "SnsClaimCheckTopic"
    And a queue named "SnsClaimCheckQueue" is subscribed to a topic named "SnsClaimCheckTopic"

  Scenario: A large message uses the claim check pattern
    Given I have a large message
    When I send the message to a topic named "SnsClaimCheckTopic"
    Then I can fetch the message from a queue named "SnsClaimCheckQueue"

#  Scenario: A small message does not use the claim check pattern
#    Given I have a small message
#    When I send the message to a topic named "SnsClaimCheckTopic"
#    Then I can fetch the message from a queue named "SqsClaimCheckQueue"
#
#  Scenario: A small message can use the claim check pattern
#    Given I have a small message that contains PHI
#    When I send the message to a topic named "SnsClaimCheckTopic"
#    Then I can fetch the message from a queue named "SqsClaimCheckQueue"
