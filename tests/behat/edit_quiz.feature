@javascript @mod_quiz @quizaccess @quizaccess_announcements
Feature: Check that fields for announcements appear in the quiz edit form and are locked as appropriate.

  Background:
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "activities" exist:
      | activity | course | section | name   |
      | quiz     | C1     | 1       | Quiz 1 |

  Scenario: Quiz settings page has announcements present and unlocked.
    When I am on the "Quiz 1" "quiz activity editing" page logged in as admin
    And I expand all fieldsets
    Then I should see "Enable live announcements"
    And the "announcements_checkinterval[number]" "field" should be disabled
    And I should see "Announcement polling interval"
    And I should see "Announcement header"
    And I set the field "announcements_use" to "1"
    And I set the field "announcements_checkinterval[number]" to "30"
    And the "announcements_checkinterval[number]" "field" should be enabled
    And I press "Save and display"
    And I should see "No questions have been added yet"

  Scenario: Quiz settings page has announcements present and locked due to admin settings.
    Given the following config values are set as admin:
      | checkinterval_locked | 1 | quizaccess_announcements |
    When I am on the "Quiz 1" "quiz activity editing" page logged in as admin
    And I expand all fieldsets
    And the "announcements_checkinterval[number]" "field" should be disabled
    And I set the field "announcements_use" to "1"
    And the "announcements_checkinterval[number]" "field" should be disabled
