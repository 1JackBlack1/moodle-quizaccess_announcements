@javascript @mod_quiz @quizaccess @quizaccess_announcements
Feature: Check that student status displays correctly.

  Background:
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | One      | student1@example.com |
      | student2 | Student   | Two      | student2@example.com |
      | student3 | Student   | Three    | student3@example.com |
      | student4 | Student   | Four     | student4@example.com |
      | student5 | Student   | Four     | student5@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
      | student2 | C1     | student |
      | student3 | C1     | student |
      | student4 | C1     | student |
      | student5 | C1     | student |
    And the following "activities" exist:
      | activity | course | section | name   |
      | quiz     | C1     | 1       | Quiz 1 |
      | quiz     | C1     | 1       | Quiz 2 |
    And quiz "Quiz 1" has announcements configured to poll every "60" seconds with a header of "<h3>Announcements</h3>"
    And quiz "Quiz 2" has announcements configured to poll every "60" seconds with a header of "<h3>Quiz 2</h3>"
    And quiz "Quiz 1" has the following student status for quizaccess_announcements:
      | username | time      |
      | student1 | 5 s ago   |
      | student2 | 30 s ago  |
      | student3 | 90 s ago  |
      | student4 | 200 s ago |

  Scenario: Monitor correctly shows student status.
    When I am on the "Quiz 1" "quizaccess_announcements > monitor" page logged in as admin
    Then the quizaccess_announcements status table should have the following statuses:
      | username | status  |
      | student1 | success |
      | student2 | success |
      | student3 | warning |
      | student4 | danger  |
      | student5 | none    |
    And quiz "Quiz 1" has announcement "New announcement" posted "20" s ago
    And quiz "Quiz 1" has the following student status for quizaccess_announcements:
      | username | time      |
      | student1 | 5 s ago   |
      | student2 | 40 s ago  |
      | student3 | 90 s ago  |
      | student4 | 200 s ago |
    And I wait "20" seconds
    And the quizaccess_announcements status table should have the following statuses:
      | username | status  |
      | student1 | success |
      | student2 | warning |
      | student3 | warning |
      | student4 | danger  |
      | student5 | none    |
    And I am on the "Quiz 2" "quizaccess_announcements > monitor" page
    And the quizaccess_announcements status table should have the following statuses:
      | username | status  |
      | student1 | none    |
      | student2 | none    |
      | student3 | none    |
      | student4 | none    |
      | student5 | none    |
      | student5 | none    |
