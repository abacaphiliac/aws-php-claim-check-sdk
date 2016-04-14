
# apply AWS credentials.
provider "aws" {
  access_key = "${var.aws_access_key}"
  secret_key = "${var.aws_secret_key}"
  region = "${var.aws_region}"
}

# create S3 bucket.
resource "aws_s3_bucket" "php-sdk-claim-check" {
  bucket = "php-sdk-claim-check"
  acl = "private"
  force_destroy = true
}

# create SNS topic.
resource "aws_sns_topic" "ClaimCheckTopic" {
  name = "ClaimCheckTopic"
}

# create SQS queue.
resource "aws_sqs_queue" "ClaimCheckQueue" {
  name = "ClaimCheckQueue"

  # create Access Policy to allow SNS topic to broadcast message to this SQS queue.
  policy = <<EOF
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Principal": {
        "AWS": "*"
      },
      "Action": "SQS:SendMessage",
      "Resource": "arn:aws:sqs:${var.aws_region}:${var.aws_account_id}:ClaimCheckQueue",
      "Condition": {
        "ArnEquals": {
          "aws:SourceArn": "${aws_sns_topic.ClaimCheckTopic.arn}"
        }
      }
    }
  ]
}
EOF
}

# Subscribe SQS queue to SNS topic.
resource "aws_sns_topic_subscription" "ClaimCheckQueue" {
  topic_arn = "${aws_sns_topic.ClaimCheckTopic.arn}"
  protocol = "sqs"
  endpoint = "${aws_sqs_queue.ClaimCheckQueue.arn}"
}


# create another SQS queue.
resource "aws_sqs_queue" "AnotherClaimCheckQueue" {
  name = "AnotherClaimCheckQueue"

  # create Access Policy to allow SNS topic to broadcast message to this SQS queue.
  policy = <<EOF
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Principal": {
        "AWS": "*"
      },
      "Action": "SQS:SendMessage",
      "Resource": "arn:aws:sqs:${var.aws_region}:${var.aws_account_id}:AnotherClaimCheckQueue",
      "Condition": {
        "ArnEquals": {
          "aws:SourceArn": "${aws_sns_topic.ClaimCheckTopic.arn}"
        }
      }
    }
  ]
}
EOF
}

# Subscribe SQS queue to SNS topic.
resource "aws_sns_topic_subscription" "AnotherClaimCheckQueue" {
  topic_arn = "${aws_sns_topic.ClaimCheckTopic.arn}"
  protocol = "sqs"
  endpoint = "${aws_sqs_queue.AnotherClaimCheckQueue.arn}"
}
