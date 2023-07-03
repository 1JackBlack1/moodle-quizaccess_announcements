@mod_quiz @quizaccess @quizaccess_announcements
Feature: Check that I can add and remove announcements.

  Background:
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "activities" exist:
      | activity | course | section | name   |
      | quiz     | C1     | 1       | Quiz 1 |
    And quiz "Quiz 1" has announcements configured to poll every "30" seconds with a header of "<h3>Announcements</h3>"

  Scenario: Quiz settings page has announcements present and unlocked.
    When I am on the "Quiz 1" "quizaccess_announcements > manage" page logged in as admin
    Then I should see "No announcements have been made."
    And I set the field "content[text]" to "A new announcement."
    And I press "Add new announcement"
    And I should not see "No announcements have been made."
    And I press "Delete all announcements"
    And I press "Yes I am sure I wish to delete the announcement(s)"
    And I should see "No announcements have been made." 
