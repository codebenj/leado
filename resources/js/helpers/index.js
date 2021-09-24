// get input error message
export const getError = (errors, field) => {
  return errors[field] ? errors[field][0] : "";
};

// get input error message
export const uppercase = text => {
  return text.toString().toUpperCase();
};

// get input error message
export const capitalize = text => {
  return (
    text
      .toString()
      .charAt(0)
      .toUpperCase() + text.slice(1)
  );
};

// show unique data from string separated by comma (,)(,)
export const showUnique = text => {
  var texts = text.split(", ");
  var count = {};
  var to_show = "";
  texts.forEach(function(i) {
    count[i] = (count[i] || 0) + 1;
  });

  for (var key in count) {
    to_show += key + "(" + count[key] + "), ";
  }

  return to_show.substring(0, to_show.length - 2);
};

// get user role and return dashboard route
export const getRoleRoute = user => {
  const role = user.user_role;

  switch (role.name) {
    case "super admin":
      return "admin.dashboard";
    case "administrator":
      return "admin.dashboard";
    case "organisation":
      return "organisation.dashboard";
    case "user":
      return "user.dashboard";
    default:
      break;
  }
};

export const isAssignedRoles = (user, roles) => {
  return roles.includes(user.user_role.name);
};

export const correctSource = source => {
  let flyer_from_a_store = ["Flyer – from a Store", "Flyer - from a Store"];
  let flyer_other = ["Flyer – Other", "Flyer - Other"];
  let flyer_in_a_letter_box = [
    "Flyer – in a Letter Box",
    "Flyer - in a Letter Box",
    "Flyer - In A Letter Box"
  ];

  if (flyer_from_a_store.indexOf(source) >= 0) {
    return "Flyer - From A Store";
  }

  if (flyer_other.indexOf(source) >= 0) {
    return "Flyer - Other";
  }

  if (flyer_in_a_letter_box.indexOf(source) >= 0) {
    return "Flyer - In A Letter Box";
  }

  return source;
};

export const getSpanArr = (data) => {
  let arr = [], pos = [];

  for (var i = 0; i < data.length; i++) {
    if (i === 0) {
      arr.push(1);
      pos = 0;
    } else {
      // Determine whether the current element is the same as the previous element
      if (data[i].name === data[i - 1].name) {
        arr[pos] += 1;
        arr.push(0);
      } else {
        arr.push(1);
        pos = i;
      }
    }
  }

  return arr
};


export const getLeadEscalationNotification = (type) => {
  switch (type) {
    case 'both':
      return ['sms', 'email', 'both']
    case 'sms':
      return ['sms']
    case 'email':
        return ['email']
    default:
      return ['sms', 'email']
  }
}

export const getLeadEscalationNotificationSelection = (type) => {
  type = JSON.stringify(type)

  switch (type) {
    case JSON.stringify(['sms', 'email', 'both']):
      return 'both'
    case JSON.stringify(['sms']):
      return 'sms'
    case JSON.stringify(['email']):
        return 'email'
    default:
      return 'both'
  }
}

// check org if escalation is manually enabled
export const isManualUpdateEnabled = (org) => {
  return (
    org &&
    org.metadata &&
    org.metadata["manual_update"] === true
  );
}

// hide enquirer information for organisation role
export const hideEnquirerInformation = (escalation_status, user) => {
  let statusToHide = ['Pending', 'Declined-Lapsed', 'Discontinued', 'Declined', 'Abandoned']

  //user is not organisation, so show info
  if(user.user_role.name != 'organisation'){
    return true
  }

  // //organisation status 0 hide lead customer information
  if(user.organisation_user.organisation.org_status == 0){
    return false
  }

  if(user.user_role.name == 'organisation'){
    return !statusToHide.some(item => item === escalation_status)
  }else{
    return true
  }
}

//ucwords like function
export const toProperName = text => {
  return text.replace(/(^\w{1})|(\s+\w{1})/g, letter => letter.toUpperCase());
}

//show organisation or not
export const hideOrganisationEscalationStatus = (escalation_status) => {
  let status = ['Supply Only', 'Supply Only(SP)', 'Unassigned', 'General Enquiry', 'General Enquiry(SP)']
  return !status.includes(escalation_status)
}

//get organisation status color
export const colorOrganisationStatus = (org) => {
  if(org.is_suspended == '0'){
    if(org.metadata && 'low_priority' in org.metadata && org.metadata['low_priority']){
      return 'warning'
    }
    return 'success'
  }
  return 'danger'
}
