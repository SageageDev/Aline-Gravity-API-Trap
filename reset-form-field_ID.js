document.addEventListener('DOMContentLoaded', function() {
  var buttons = document.querySelectorAll('.clear-input-id-button');

  buttons.forEach(function(button) {
    button.addEventListener('click', function() {
      var fieldName = button.getAttribute('data-field-name');
      var fieldId = button.getAttribute('data-field-id');

      console.log('Clearing field:', fieldName, fieldId);
    });
  });

  // Get the container element
  const fieldIdContainer = document.getElementById('field-id-container');

  // Make the container element visible
  fieldIdContainer.style.display = 'block';

  // Get the add button
  const addFieldIdButton = document.getElementById('add-field-id-button');

  // Set the type attribute to button
  addFieldIdButton.type = 'button';

  // Function to retrieve existing form IDs and field settings from the database
  function getExistingFormSettings() {
    fetch('/wp-admin/admin-ajax.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        action: 'get_existing_form_settings',
      }),
    })
    .then((response) => response.json())
    .then((data) => {
      if (data.form_ids && data.field_settings) {
        // Populate the "Selected Form IDs" section
        const selectedFormIdsContainer = document.getElementById('selected-form-ids');
        selectedFormIdsContainer.innerHTML = '';
        data.form_ids.forEach((formId) => {
          const formIdHtml = `
            <div>
              <span>Form ID: ${formId}</span>
            </div>
          `;
          selectedFormIdsContainer.insertAdjacentHTML('beforeend', formIdHtml);
        });

        // Populate the "Input IDs" section
        const inputIdsContainer = document.getElementById('input-ids');
        inputIdsContainer.innerHTML = '';
        data.field_settings.forEach((fieldSetting) => {
          const fieldIdHtml = `
            <div>
              <span>Field Name: ${fieldSetting.name} = ${fieldSetting.id}</span>
            </div>
          `;
          inputIdsContainer.insertAdjacentHTML('beforeend', fieldIdHtml);
        });
      }
    })
    .catch((error) => console.error(error));
  }

  // Call the function to retrieve existing form settings on page load
  getExistingFormSettings();

  // New code snippet
  jQuery.ajax({
    type: 'POST',
    url: ajax_object.ajax_url,
    data: {
      'action': 'get_existing_form_settings'
    },
    success: function(response) {
      if (response.form_ids.length > 0 && response.field_settings.length > 0) {
        // Display the existing form settings
        var html = '';
        response.form_ids.forEach(function(form_id) {
          html += '<h4>Form ID: ' + form_id + '</h4>';
          Object.keys(response.field_settings).forEach(function(field_name) {
            html += '<p>Field Name: ' + field_name + ', Field ID: ' + response.field_settings[field_name] + '</p>';
          });
        });
        document.getElementById('existing-form-settings').innerHTML = html;
      } else {
        // Display a message if no settings are found
        document.getElementById('existing-form-settings').innerHTML = 'No settings found yet.';
      }
    }
  });
});