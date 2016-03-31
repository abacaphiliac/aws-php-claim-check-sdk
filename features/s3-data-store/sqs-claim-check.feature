# language: en
@sqs @s3
Feature: SQS+S3 Claim Check
  
  Background:
    Given a data store named "sns-claim-check"
    And a queue named "SqsClaimCheckQueue"

  Scenario: A large message uses the claim check pattern
    Given I have a large message
    When I send the message to a queue named "SqsClaimCheckQueue"
    Then I can fetch the message from a queue named "SqsClaimCheckQueue"

#  Scenario: A small message does not use the claim check pattern
#    Given I have a small message
#    When I send the message to a queue named "SqsClaimCheckQueue"
#    Then I can fetch the message from a queue named "SqsClaimCheckQueue"
#
#  Scenario: A small message can use the claim check pattern
#    Given I have a small message that contains PHI
#    When I send the message to a queue named "SqsClaimCheckQueue"
#    Then I can fetch the message from a queue named "SqsClaimCheckQueue"
