<?php

return [

    'name' => env('HRIS_NAME', 'HRIS Indonesia'),
    'default_password' => env('HRIS_DEFAULT_PASSWORD', '1234567890'),
    'upload_destination' => env('HRIS_UPLOAD_DESTINATION', '/uploads'),
    'profile_image_upload_path' => env('HRIS_PROFILE_IMAGE_UPLOAD_PATH', '/images/profiles'),

    'format' => [
        'date' => env('HRIS_DATE_FORMAT', 'd-m-Y'),
        'date_long' => env('HRIS_DATE_FORMAT_LONG', 'dd-mm-yyyy'),
        'date_intl' => env('HRIS_DATE_FORMAT_INTL', 'dd-MM-yyyy'),
        'query_date' => env('HRIS_QUERY_DATE_FORMAT', 'Y-m-d'),
        'time' => env('HRIS_TIME_FORMAT', 'H:i:s'),
        'hour' => env('HRIS_HOUR_FORMAT', 'H:i'),
        'first_date' => env('HRIS_FIRST_DATE_FORMAT', '01-m-Y'),
        'last_date' => env('HRIS_LAST_DATE_FORMAT', 't-m-Y'),
        'date_time' => env('HRIS_DATE_TIME_FORMAT', 'd-m-Y H:i:s'),
    ],

    'workday_per_week' => (int) env('HRIS_WORKDAY_PER_WEEK', 5),
    'offday_per_week' => explode(',', env('HRIS_OFFDAY_PER_WEEK', '6,7')),
    'record_per_page' => (int) env('HRIS_RECORD_PER_PAGE', 17),
    'max_record_per_page' => (int) env('HRIS_MAX_RECORD_PER_PAGE', 99),

    'encryption' => [
        'private_key_path' => env('HRIS_PRIVATE_KEY_PATH', '/config/key/private.pem'),
        'public_key_path' => env('HRIS_PUBLIC_KEY_PATH', '/config/key/public.pem'),
        'passphrase' => env('HRIS_PASSPHRASE', '01BVZD4XBVC9W2SQTK5NK2173Q'),
    ],

    'currency' => [
        'prefix' => env('HRIS_CURRENCY_PREFIX', 'Rp.'),
        'suffix' => env('HRIS_CURRENCY_SUFFIX', ''),
        'decimal_precision' => (int) env('HRIS_DECIMAL_PRECISION', 2),
        'decimal_point' => env('HRIS_DECIMAL_POINT', ','),
        'thousand_separator' => env('HRIS_THOUSAND_SEPARATOR', '.'),
    ],

    'security' => [
        'config_menu' => env('HRIS_SECURITY_CONFIG_MENU', 'ROLE_SUPER_ADMIN'),
        'user_menu' => env('HRIS_SECURITY_USER_MENU', 'ROLE_HRSTAFF'),
        'master_menu' => env('HRIS_SECURITY_MASTER_MENU', 'ROLE_HRSTAFF'),
        'company_menu' => env('HRIS_SECURITY_COMPANY_MENU', 'ROLE_HRSTAFF'),
        'employee_menu' => env('HRIS_SECURITY_EMPLOYEE_MENU', 'ROLE_HRSTAFF'),
        'personal_menu' => env('HRIS_SECURITY_PERSONAL_MENU', 'ROLE_EMPLOYEE'),
        'address_menu' => env('HRIS_SECURITY_ADDRESS_MENU', 'ROLE_HRSTAFF'),
        'attendance_menu' => env('HRIS_SECURITY_ATTENDANCE_MENU', 'ROLE_HRSTAFF'),
        'overtime_menu' => env('HRIS_SECURITY_OVERTIME_MENU', 'ROLE_HRSTAFF'),
        'leave_menu' => env('HRIS_SECURITY_LEAVE_MENU', 'ROLE_HRSTAFF'),
        'payroll_menu' => env('HRIS_SECURITY_PAYROLL_MENU', 'ROLE_HRSUPERVISOR'),
    ],

    'attendance' => [
        'upload_path' => env('HRIS_ATTENDANCE_UPLOAD_PATH', '/attendances'),
        'cut_off_date' => (int) env('HRIS_ATTENDANCE_CUT_OFF_DATE', -1),
        'default_absent_reason_code' => env('HRIS_ATTENDANCE_DEFAULT_ABSENT_REASON_CODE', 'ABS'),
    ],

    'overtime' => [
        'auto_approved' => (bool) env('HRIS_OVERTIME_AUTO_APPROVED', true),
        'invalid_message' => env('HRIS_OVERTIME_INVALID_MESSAGE', 'hris.invalid_data'),
        'upload_path' => env('HRIS_OVERTIME_UPLOAD_PATH', '/overtimes'),
        'benefit_code' => env('HRIS_OVERTIME_BENEFIT_CODE', 'OT'),
    ],

    'bpjs' => [
        'jkk_code' => env('HRIS_PAYROLL_BPJS_JKK_CODE', 'JKK'),
        'jkm_code' => env('HRIS_PAYROLL_BPJS_JKM_CODE', 'JKM'),
        'jht_plus_code' => env('HRIS_PAYROLL_BPJS_JHT_CODE_PLUS', 'JHTP'),
        'jht_minus_code' => env('HRIS_PAYROLL_BPJS_JHT_CODE_MINUS', 'JHTM'),
        'jht_company_code' => env('HRIS_PAYROLL_BPJS_JHT_CODE_COMPANY', 'JHTC'),
        'jp_plus_code' => env('HRIS_PAYROLL_BPJS_JP_CODE_PLUS', 'JPP'),
        'jp_minus_code' => env('HRIS_PAYROLL_BPJS_JP_CODE_MINUS', 'JPM'),
        'jp_company_code' => env('HRIS_PAYROLL_BPJS_JP_CODE_COMPANY', 'JPC'),
    ],

    'tax' => [
        'plus_code' => env('HRIS_TAX_PLUS_CODE', 'PPH21P'),
        'minus_code' => env('HRIS_TAX_MINUS_CODE', 'PPH21M'),
    ],

];