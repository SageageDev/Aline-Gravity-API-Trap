/*This script goes on the form. 
It parses the URL and gets the community name and unique name.

This script will need to be tailored per community if there is more than one community.
Else if ther is only one this script can be ignored and the community name and unique name can be hard coded.
*/

document.addEventListener("DOMContentLoaded", function() {
  var url = window.location.href;

  var communityName;
  var uniqueName;

  if (url.includes("/our-locations/westminster-place/")) {
    communityName = "Evanston";
    uniqueName = "WestminsterPlace";
  } else if (url.includes("/our-locations/lake-forest-place/")) {
    communityName = "Lake Forest Place";
    uniqueName = "LakeForestPlacePH";
  } else if (url.includes("/our-locations/the-moorings/")) {
    communityName = "Moorings";
    uniqueName = "TheMooringsPH";
  } else if (url.includes("/our-locations/ten-twenty-grove/")) {
    communityName = "Ten Twenty Grove";
    uniqueName = "PresHomesTenTwentyGrove";
  } else if (!url.includes("/our-locations/") && url.includes("https://www.presbyterianhomes.org/") || url.includes("https://presbyteriadev.wpenginepowered.com/") || url.includes("https://presbyteriastage.wpenginepowered.com/")) {
    communityName = "Presbyterian Homes Corporate";
    uniqueName = "PresbyterianHomesCorporate";
  }

  if (communityName && uniqueName) {
    var communityInput = document.getElementById("input_29_20");
    var uniqueInput = document.getElementById("input_29_18");

    if (communityInput && uniqueInput) {
      communityInput.setAttribute("value", communityName);
      uniqueInput.setAttribute("value", uniqueName);
    }
  }
});