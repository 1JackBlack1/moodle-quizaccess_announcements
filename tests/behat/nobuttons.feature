@mod_quiz @quizaccess @quizaccess_announcements
Feature: Check that buttons associated with announcements don't appear when not set up

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

  Scenario: Quiz view page does not contain buttons for students.
    When I am on the "Quiz 1" "mod_quiz > view" page logged in as student
    Then I should not see "Add or manage announcements"
    And I should not see "Monitor student status"
    And I press "Attempt quiz"
    And I should not see "Add or manage announcements"
    And I should not see "Monitor student status"

  Scenario: Quiz view page does not contain buttons for admin.
    When I am on the "Quiz 1" "mod_quiz > view" page logged in as admin
    Then I should not see "Add or manage announcements"
    And I should not see "Monitor student status"
    And I press "Preview quiz"
    And I should not see "Add or manage announcements"
    And I should not see "Monitor student status"
