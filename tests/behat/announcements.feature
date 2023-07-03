@javascript @mod_quiz @quizaccess @quizaccess_announcements
Feature: Check that announcements appear in a quiz.

  Background:
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "users" exist:
      | username | firstname | lastname | email               |
      | student  | Student   | One      | student@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student  | C1     | student |
    And the following "activities" exist:
      | activity | course | section | name   |
      | quiz     | C1     | 1       | Quiz 1 |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype       | name  | questiontext               |
      | Test questions   | truefalse   | TF1   | Text of the first question |
    And quiz "Quiz 1" contains the following questions:
      | question | page |
      | TF1      | 1    |
    And quiz "Quiz 1" has announcements configured to poll every "10" seconds with a header of "<h3>Announcements</h3>"

  Scenario: Quiz does not contain buttons for students.
    When I am on the "Quiz 1" "mod_quiz > view" page logged in as student
    And I press "Attempt quiz"
    Then I should see "Announcements"
    And I should see "No announcements have been made."
    And quiz "Quiz 1" has announcement "New announcement" posted
    And I wait "10" seconds
    And I should see "New announcement" in the "A new announcement has been made" "dialogue"
    And I reload the page
    And I should not see "No announcements have been made."
    And I should see "New announcement" in the "A new announcement has been made" "dialogue"
    And I wait "10" seconds
    And I reload the page
    And I should not see "A new announcement has been made"
    And I should see "New announcement"
