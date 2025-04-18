<?php
// App Default Date and Time format at the time of GET
const DATETIME_FORMAT = 'd/m/Y H:i:s';
const DATETIME_WITHOUT_SECONDS_FORMAT = 'd/m/Y H:i';
const DATE_FORMAT = 'd/m/Y';
const TIME_FORMAT = 'H:i:s';
const TIME_WITHOUT_SECONDS_FORMAT = 'H:i';
const TIME_FORMAT_WITHOUT_SECONDS = 'H:i';
const DATE_TIME_FORMAT_FOR_APP = 'Y-m-d H:i:s';
const DATE_FORMAT_FOR_APP = 'Y-m-d';
const STATIC_TIME_FORMAT_FOR_REPORT = '%a day(s) %H:%I:%S';
const STATIC_TIME_FORMAT_FOR_REPORT_WITHOUT_DAYS = '%H:%I:%S';
const START_OF_MINUTE = 0;
const END_OF_MINUTE = 59;
const DATETIME_WITHOUT_SECONDS_FORMAT_FOR_APP = 'Y-m-d H:i';
const DATETIME_WITHOUT_SECONDS_FORMAT_FOR_APP_FOR_VENDOR_AGREEMENT = 'j F, Y';
const DATE_FORMAT_FOR_VENDOR_AGREEMENT_MONTH = 'F j, Y';
const DATE_FORMAT_YEAR = 'Y';


// Gender Types
const MALE = 'male';
const FEMALE = 'female';
const OTHERS = 'others';

const GENDER = [MALE, FEMALE, OTHERS];

//Blood Groups
const A1_P = 'a1_positive';
const A1_N = 'a1_negative';
const A2_P = 'a2_positive';
const A2_N = 'a2_negative';
const B_P = 'b_positive';
const B_N = 'b_negative';
const A1B_P = 'a1b_positive';
const A1B_N = 'a1b_negative';
const A2B_P = 'a2b_positive';
const A2B_N = 'a2b_negative';
const AB_P = 'ab_positive';
const AB_N = 'ab_negative';
const O_P = 'o_positive';
const O_N = 'o_negative';
const A_P = 'a_positive';
const A_N = 'a_negative';

const BLOOD_GROUPS = [A1_P, A1_N, A2_P, A2_N, B_P, B_N, A1B_P, A1B_N, A2B_P, A2B_N, AB_P, AB_N, O_P, O_N, A_P, A_N];

const  ACTIVE = 'Active';
const  INACTIVE = 'Inactive';
const STATUS = [ACTIVE, INACTIVE];

const  YES = 'Yes';
const  NO = 'No';
const VERIFIED = [YES, NO];

const SUPER_ADMIN_ROLE_NAME = 'Super Admin';
const SITE_SUPERVISOR_ROLE_NAME = 'Site Supervisor';
const ENGINEER_ROLE_NAME = 'Engineer';
const VENDOR_ROLE_NAME = 'Vendor';
const CUSTOMER_ROLE_NAME = 'Customer';
const LABOUR_ROLE_NAME = 'Labour';
const CONTRACTOR_ROLE_NAME = 'Contractor';
const ACCOUNTANT_ROLE_NAME = 'Accountant';
const USER_ROLE_NAME = 'User';
const SYSTEM_ROLES = [SUPER_ADMIN_ROLE_NAME, CUSTOMER_ROLE_NAME, VENDOR_ROLE_NAME, SITE_SUPERVISOR_ROLE_NAME, LABOUR_ROLE_NAME, CONTRACTOR_ROLE_NAME, ACCOUNTANT_ROLE_NAME, USER_ROLE_NAME, ENGINEER_ROLE_NAME];

const CREATED = 'Created';
const IN_PROGRESS = 'In-progress';
const ON_HOLD = 'On-hold';
const COMPLETED = 'Completed';
const DELETED = 'Deleted';

const PROJECT_TASK_STATUSES = [CREATED, IN_PROGRESS, ON_HOLD, COMPLETED, DELETED];

const PROJECT_STATUSES = [IN_PROGRESS, ON_HOLD, COMPLETED];

CONST PENDING = 'pending';
CONST REJECTED = 'rejected';

const PURCHASE_REQUEST_STATUSES = [PENDING, COMPLETED, REJECTED];

const OPEN = 'open';
const CLOSED = 'closed';
const SUPPORT_TICKET_STATUSES = [OPEN, CLOSED];

const ALL = 'All';
const BILLING = 'Billing';
const TECHNICAL = 'Technical';
const SUPPORT_TYPES = [ALL, BILLING, TECHNICAL, OTHERS];

