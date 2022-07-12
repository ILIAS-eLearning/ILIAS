SkillEntries =
{
  showNonLatest: function (element)
  {
    var non_latest = element.closest(".ilSkillEntriesLatest").nextElementSibling;
    non_latest.style.display = "block";

    var all_button = element;
    all_button.parentElement.style.display = "none";
  },
  hideNonLatest: function (element)
  {
    var non_latest = element.closest(".ilSkillEntriesNonLatest");
    non_latest.style.display = "none";

    var all_button = element.closest(".ilSkillEntriesNonLatest").previousElementSibling.querySelector(".ilSkillEntriesAllButton");
    all_button.style.display = "block";
  }
};