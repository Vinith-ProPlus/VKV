<?php

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Foundation\Application;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Random\RandomException;

    /**
     * Get carbon from date string.
     *
     * @param $date
     * @return Carbon
     */
    function getCarbon($date)
    {
        $format = 'Y-m-d H:i:s';
        $isStartOfDay = $isEndOfMonth = $formatForMonthYear = false;
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $date)) {
            $format = 'Y-m-d';
            $isStartOfDay = true;
        } elseif (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date)) {
            $format = 'd/m/Y';
            $isStartOfDay = true;
        } elseif (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})(?:\s+(\d{2}):(\d{2}):(\d{2}))?$/', $date)) {
            $format = 'd/m/Y H:i:s';
        } elseif (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})(?:\s+(\d{2}):(\d{2}))?$/', $date)) {
            $format = 'd/m/Y H:i';
        } elseif (preg_match('/^(\d{1,2})\/(\d{4})$/', $date)) {
            $format = 'm/Y';
            $formatForMonthYear = true;
            $isEndOfMonth = true;
        } elseif (preg_match('/^(\d{1,2}):(\d{1,2}):(\d{1,2})$/', $date)) {
            $format = 'H:i:s';
        }

        if ($formatForMonthYear) {
            list($month, $year) = explode('/', $date);
            $date = now()->startOfMonth()->month($month)->year($year);
        } else {
            $date = createCarbonFromFormat($format, $date);
        }

        if ($isStartOfDay) {
            $date->startOfDay();
        }
        if ($isEndOfMonth) {
            $date->endOfMonth();
        }
        return $date;
    }

    function getDateFromTimeStamp($timestamp)
    {
        return date("Y-m-d H:i:s", $timestamp);
    }

    /**
     * check field is empty
     * @param $field
     * @return mixed
     */
    function getDisplayValue($field)
    {
        return isNullOrEmpty($field) ? '-' : $field;
    }

    /**
     * Enable phone number tooltip
     *
     * @param $phoneNumber
     * @param $canShowPhoneNumber
     * @return string
     */
    function displayToolTipForThePhoneNumber($phoneNumber, $canShowPhoneNumber)
    {
        $maskedNumber = maskPhoneNumber($phoneNumber);
        if (empty($maskedNumber)) {
            return null;
        }

        return $maskedNumber . ($canShowPhoneNumber ? "<a class=' tip' title='$phoneNumber' onmouseover='displayPhoneNumber(this)'> <i class='icon-info'></i></a>" : "");
    }

    /**
     * Mask the phone number.
     *
     * @param $phoneNumber
     * @return string
     */
    function maskPhoneNumber($phoneNumber)
    {
        return !empty($phoneNumber) ? substr($phoneNumber, 0, 4) . str_repeat('*', 4) . substr($phoneNumber, 8, 10) : null;
    }

    /**
     * Check date field is empty
     * If it is date means, return as d/m/Y.
     * else return '-'.
     * @param $field
     * @param bool $isDisplay
     * @param string $format
     * @return mixed
     */
    function getDateString($field, $isDisplay = true, $format = DATE_FORMAT)
    {
        return isNullOrEmpty($field) ? ($isDisplay ? '-' : null) : date($format, strtotime($field));
    }

    /**
     * Check date field is empty
     * If it is date means, return as d/m/Y.
     * else return '-'.
     * @param $field
     * @param $isDisplay
     * @return mixed
     */
    function getDateYearString($field, $isDisplay = true)
    {
        return isNullOrEmpty($field) ? ($isDisplay ? '-' : null) : date(DATE_FORMAT_YEAR, strtotime($field));
    }

    /**
     * Check date field is empty
     * If it is date means, return as d/m/Y.
     * else return '-'.
     * @param $field
     * @param bool $isDisplay
     * @param string $format
     * @return mixed
     */
    function getTimeString($field, $isDisplay = true)
    {
        return isNullOrEmpty($field) ? ($isDisplay ? '-' : null) : gmdate(TIME_FORMAT, $field);
    }


    function showDayHourMinFormat($value, $inputFormat = false, $twoDigitPrecision = false)
    {
        $value = !empty($value) ? $value : 0;
        if (strtolower($inputFormat) == 's') {
            $value = floor($value / 60);
        }
        $hours = floor($value / 60);
        $minutes = floor($value % 60);
        if ($twoDigitPrecision) {
            return sprintf("%02d", $hours) . ($hours <= 1 ? ' Hr ' : ' Hrs ') . sprintf("%02d", $minutes) . ($minutes <= 1 ? ' Min' : ' Mins');
        }
        return $hours . ($hours <= 1 ? ' Hr ' : ' Hrs ') . $minutes . ($minutes <= 1 ? ' Min' : ' Mins');

    }

    /**
     * check field is empty or null and set value 0
     * @param $value
     * @return mixed
     */
    function getNumericValue($value)
    {
        return isNullOrEmpty($value) ? 0 : $value;
    }

    /**
     * Check whether the value is null or empty
     *
     * @param $value
     * @return bool
     */
    function isNullOrEmpty($value)
    {
        return ($value == '' || $value == null);
    }

    /**
     * For yajra datatable service
     *
     * @param bool $canDownloadExcelWithoutPermission
     * @param null $reportType
     * @param null $reportName
     * @return array
     */
    function commonHtmlParametersForDataTable($canDownloadExcelWithoutPermission = false, $reportType = null, $reportName = null)
    {
        $parameters = commonHtmlParametersForYajraDataTable();
        $parameters['iDeferLoading'] = 0;
        $parameters['buttons'] = ['reload'];

        /**
         * The user need the excel download permission or $isNeedExcelDownloadButton as true.
         */
        if ($canDownloadExcelWithoutPermission || canDownloadExcel($reportType, $reportName)) {
            $parameters['buttons'] = ['excel', 'reload'];
        }

        return $parameters;
    }

    /**
     * Check whether the authenticated user could have the permission to download the excel for given report details.
     *
     * @param $reportType
     * @param $reportName
     * @return bool
     */
    function canDownloadExcel($reportType, $reportName)
    {
        if ($reportType && $reportName) {
            $permission = reportPermissionConfig($reportType, $reportName);
            return $permission && auth()->user()->may($permission['download']);
        }

        return false;
    }

    /**
     * Get report permission details from config.
     *
     * @param null $reportType
     * @param null $key
     * @return mixed
     */
    function reportPermissionConfig($reportType = null, $key = null)
    {
        $reportType = $reportType ? ('.' . $reportType) : '';
        $key = $key ? ('.' . $key) : '';
        return config('report-permissions' . $reportType . $key);
    }

    /**
     * @return array
     */
    function commonHtmlParametersForYajraDataTable()
    {
        return [
            'pagingType' => 'input',
            'paging' => true,
            'lengthChange' => true,
            'searching' => true,
            'processing' => false,
            'autoWidth' => false,
            'retrieve' => true,
            'ordering' => true,
            'order' => [[1, 'desc']],
            'destroy' => true,
            'dom' => '<"datatable-header"lB>r<"clear"><"datatable-scroll"t><"bottom"ip><"clear">',
            'responsive' => true,
            'serverSide' => true,
        ];
    }

    /*
     * Convert value to rupee
     */
    function convertToRupees($value)
    {
        return '₹' . number_format($value, 2);
    }

    /*
     * Convert given Hour Minutes Seconds for seconds
     * @param time
     */
    function convertTimeToSeconds($time)
    {
        $time = date('H:i:s', strtotime($time));
        $explodeValues = explode(':', $time);
        return (($explodeValues[0] * 60 * 60) + ($explodeValues[1] * 60) + $explodeValues[2]);
    }

    function base64StringOfRedTaxiLogo()
    {
        return "data:image/png;base64,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";
    }

    /**
     * Set the from and to date for reports. default from date is calculated using given hours
     *
     * @param $inputFromDate
     * @param $inputToDate
     * @param int $defaultFromDateCalculationTimeInHours
     * @return array
     */
    function setFromAndToDateTime($inputFromDate, $inputToDate, $defaultFromDateCalculationTimeInHours = 1)
    {
        $dateBefore = Carbon::now()->subHour($defaultFromDateCalculationTimeInHours);
        $currentDateTime = Carbon::now();
        $fromDate = getCarbonFromDateString($inputFromDate);
        $fromDate = ($fromDate == null) ? $dateBefore : $fromDate;
        $toDate = getCarbonFromDateString($inputToDate);
        $toDate = ($toDate == null) ? $currentDateTime : $toDate;
        return [$fromDate, $toDate];
    }

    /**
     * Set the from and to date for reports. Default values for from and to are today and now.
     * @param $inputFromDate
     * @param $inputToDate
     * @return array
     */
    function setDateTimeForReports($inputFromDate, $inputToDate)
    {
        $fromDate = (getCarbonFromDateString($inputFromDate) ?: today()->subDays(FROM_TO_DATE_DIFFERENT_IN_DAYS));
        $toDate = getCarbonFromDateString($inputToDate);
        $toDate = ($toDate ? $toDate->addSeconds(59) : now());
        return [$fromDate, $toDate];
    }

    /**
     * Returns the difference between two dates in H:i:s format
     * If the first date is greater than second date, then it will return null value.
     * Carbon don't have default method to show the days in hours, so we processed in seconds.
     *
     * @param $firstDate
     * @param $secondDate
     * @return null|string
     */
    function getDateDifference($firstDate, $secondDate)
    {
        if (!empty($firstDate) && !empty($secondDate)) {
            $diffInSeconds = Carbon::parse($firstDate)->diffInSeconds(Carbon::parse($secondDate));
            if ($firstDate < $secondDate) {
                return formatTime($diffInSeconds);
            }
        }
        return null;
    }

    /**
     * Get the time for H:i:s (or) given format.
     *
     * @param $seconds
     * @param string $format
     * @return string
     */
    function formatTime($seconds, $format = ':i:s')
    {
        return sprintf("%02d", floor($seconds / 3600)) . gmdate($format, $seconds % 3600);
    }

    /**
     * Get the random string
     *
     * @return string
     */
    function getRandomString()
    {
        return md5(uniqid(rand(), true));
    }

    /**
     * This method is used to convert the input values to uppercase.
     * Using in:- CallTakerBookingController@dispatchInformation, BookingController@update, CallTakerBookingRequest@all
     *
     * @param $input
     * @param $keys
     * @return mixed
     */
    function convertStringToUpperCase($input, $keys)
    {
        foreach ($keys as $key) {
            if (!empty($input[$key])) {
                $input[$key] = strtoupper($input[$key]);
            }
        }
        return $input;
    }


    /**
     * Cast the given value to yes or no for the given value is true or false  respectively
     *
     * @param $value
     * @return string
     */
    function castToYesOrNo($value)
    {
        return $value ? 'Yes' : 'No';
    }

    if (!function_exists('optional')) {
        /**
         * Provide access to optional objects.
         *
         * @param mixed $value
         * @return mixed
         */
        function optional($value)
        {
            return new Optional($value);
        }
    }

    /**
     * String to upper and removes spaces
     *
     * @param $value
     * @return mixed
     */
    function stringToUpperAndRemoveSpaces($value)
    {
        return str_replace(' ', '', strtoupper($value));
    }


    /**
     * Make carbon instance with current date (start of time)
     * Do not use startOfDay() multiple times in single method.
     * Prefer saving startOfDay() in a variable for using multiple times in a single method
     *
     * @return Carbon static
     */
    function startOfDay()
    {
        return today();
    }


    /**
     * Converts the given array to a string using implode method
     *
     * @param array $value
     * @return string
     */
    function arrayToString($value = [])
    {
        return implode(', ', $value);
    }

    /**
     * Error response for model not found exception
     * This will make the message only
     *
     * @param $error
     * @return JsonResponse
     */
    function errorResponseForModelNotFound($error)
    {
        $names = explode('\\', $error->getModel());
        return response()->json(['error' => last($names) . ' not found.'], 404);
    }

    /**
     * Make the url as a valid one by appending prefix ('//') if needed
     *
     * @param $value
     * @return string
     */
    function makeValidUrl($value)
    {
        $scheme = parse_url($value, PHP_URL_SCHEME);
        return $scheme ? $value : ('//' . $value);
    }

    /**
     * Make time format by parsing the given time
     *
     * @param $value
     * @param int|null $changeSeconds
     * @return mixed
     */
    function makeTimeFormat($value, int $changeSeconds = null)
    {
        $value = Carbon::parse($value);
        if ($changeSeconds) {
            $value->second($changeSeconds);
        }
        return $value->format(TIME_FORMAT);
    }

    /**
     * Make as given seconds into H:i:s format
     *
     * @param $seconds
     * @return string
     */
    function showHourMinuteSecondsFormat($seconds)
    {
        return sprintf("%02d", floor($seconds / 3600)) . gmdate(":i:s", $seconds % 3600);
    }

    /**
     * Show day hour min format for input in seconds
     *
     * @param $value
     * @return string
     */
    function showDayHourMinFormatFromSeconds($value)
    {
        return showDayHourMinFormat($value, 's', true);
    }

    /**
     * Get start of month
     *
     * @return Carbon static
     */
    function startOfMonth()
    {
        return now()->startOfMonth();
    }

    /**
     * Get end of month
     *
     * @return Carbon static
     */
    function endOfMonth()
    {
        return now()->endOfMonth();
    }

    /**
     * Get list of the month
     *
     * @return array
     */
    function getListOfMonth()
    {
        return [
            '01' => 'January',
            '02' => 'February',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        ];
    }

    /**
     * Get month string from given month value.
     *
     * @param $month
     * @return bool|mixed
     */
    function findMonthName($month)
    {
        return (!empty(getListOfMonth()[$month]) ? getListOfMonth()[$month] : false);
    }

    /**
     * Convert now() into given format.
     *
     * @param $input
     * @return string
     */
    function formatNow($input)
    {
        return now()->format($input);
    }

    function logError($error, $message, $location, $params = [])
    {
        commonLogError($location, $error->getMessage(), $params, $error->getTraceAsString(), $message);
    }

    /**
     * @param $message
     * @param $location
     * @param $errorMessage
     * @param $params
     * @param array $errorTrace
     */
    function commonLogError($location, $errorMessage, $params, $errorTrace = [], $message = 'error')
    {
        Log::error([
            $message => [
                'location' => $location,
                'message' => $errorMessage,
                'params' => $params,
                'trace' => $errorTrace,
            ],
        ]);
    }

    /**
     * Set the dataTable with empty row(s).
     *
     * @param $dataTables
     * @param array $withParams
     * @return mixed
     */
    function emptyDataTable($dataTables, $withParams = [])
    {
        return $dataTables->collection(collect())->with($withParams)->make(true);
    }


    /**
     * Map the given collection with the given key value as the array of each item key
     *
     * @param array $collection
     * @param $key
     *
     * @return array
     */
    function mapWithCustomKey(array $collection, $key)
    {
        $items = [];
        foreach ($collection as $each) {
            $items[$each[$key]] = $each;
        }
        return $items;
    }


    /**
     * Get a random value from the given array
     *
     * @param array $arr
     *
     * @return mixed
     */
    function getRandom(array $arr)
    {
        return $arr[array_rand($arr)];
    }

    function formatTimeForReports($startTime, $endTime)
    {
        $startTime = makeTimeFormat(($startTime ?: '00:00'), START_OF_MINUTE);
        $endTime = makeTimeFormat($endTime, END_OF_MINUTE); // If end time is null means the time format will result current time.
        return [$startTime, $endTime];
    }

    /**
     * Parse the given datetime.
     * If the input datetime is provided with date alone,
     * Then the carbon parse will set the start date.
     *
     * @param $datetime
     * @return mixed
     */
    function parse($datetime)
    {
        return Carbon::parse($datetime);
    }

    /**
     * Total days for current month.
     *
     * @return int
     */
    function totalDaysForCurrentMonth()
    {
        return (int)now()->endOfMonth()->format('d');
    }

    /**
     * Cast the given rupees to words.
     *
     * @param $number
     * @return string
     */
    function castRupeesInWords($number)
    {
        $no = floor($number);
        $decimal = round(($number - $no), 2) * 100;
        $digits_length = strlen($no);
        $i = 0;
        $str = [];
        $words = [
            0 => '',
            1 => 'One',
            2 => 'Two',
            3 => 'Three',
            4 => 'Four',
            5 => 'Five',
            6 => 'Six',
            7 => 'Seven',
            8 => 'Eight',
            9 => 'Nine',
            10 => 'Ten',
            11 => 'Eleven',
            12 => 'Twelve',
            13 => 'Thirteen',
            14 => 'Fourteen',
            15 => 'Fifteen',
            16 => 'Sixteen',
            17 => 'Seventeen',
            18 => 'Eighteen',
            19 => 'Nineteen',
            20 => 'Twenty',
            30 => 'Thirty',
            40 => 'Forty',
            50 => 'Fifty',
            60 => 'Sixty',
            70 => 'Seventy',
            80 => 'Eighty',
            90 => 'Ninety'];
        $digits = ['', 'Hundred', 'Thousand', 'Lakh', 'Crore'];
        while ($i < $digits_length) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += $divider == 10 ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $str[] = ($number < 21) ? $words[$number] . ' ' . $digits[$counter] . $plural : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . $plural;
            } else {
                $str[] = null;
            }
        }
        $Rupees = implode(' ', array_reverse($str));
        $paise = $decimal ? ('Rupees And ' . appendSpace($words[$decimal / 10]) . appendSpace($words[$decimal % 10]) . 'Paise ') : '';
        return ($Rupees ? $Rupees : 'Zero ') . $paise . 'Only';
    }

    /**
     * Append a empty space if the given value is not empty.
     *
     * @param $value
     * @return null|string
     */
    function appendSpace($value)
    {
        if ($value) {
            return $value . ' ';
        }

        return null;
    }


    /**
     * Get the current route name.
     *
     * @return mixed
     */
    function routeName()
    {
        return Route::currentRouteName();
    }

    /**
     * Error response for the vendor app.
     *
     * @param $code
     * @param $message
     * @param string $name
     * @return array
     */
    function vendorAppCommonErrorResponse($code, $message, $name = 'common_error')
    {
        return [
            'error' => commonErrorResponse($code, $message, $name),
        ];
    }

    /**
     * Common error response.
     *
     * @param $code
     * @param $message
     * @param string $name
     * @return array
     */
    function commonErrorResponse($code, $message, $name = 'common_error')
    {
        return [
            $name => [
                [
                    'code' => $code,
                    'message' => $message,
                ],
            ],
        ];
    }

    /**
     * Get the number masking url for the given key
     *
     * @param $key
     * @return mixed
     */
    function numberMaskingConfig($key)
    {
        return configEnv('customer_number_masking.' . $key);
    }

    /**
     * Success response in json.
     *
     * @return JsonResponse
     */
    function successResponse()
    {
        return response()->json(['message' => 'success']);
    }


    /**
     * Replace '_' in snake case and converting it to title case
     *
     * @param $string
     * @return string
     */
    function snakeCaseToTitleCase($string)
    {
        return ucwords(str_replace('_', ' ', $string));
    }

    /**
     * Get list of the Dates
     *
     * @return array
     */
    function getListOfDate()
    {
        return [
            '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19',
            '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31',
        ];
    }

    /**
     * Get list of the Hours
     *
     * @return array
     */
    function getListOfHours()
    {
        return [
            '00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19',
            '20', '21', '22', '23',
        ];
    }

    /**
     * round the given amount.
     *
     * @param $amount
     * @return float|int
     */
    function roundAmount($amount)
    {
        return !empty($amount) ? round($amount) : 0;
    }


    /**
     * Get previous month value
     *
     * @return Carbon
     */
    function previousMonth()
    {
        return startOfMonth()->subMonth();
    }

    /**
     * Subtract months from now
     *
     * @param $count
     * @return Carbon
     */
    function subMonthsFromNow($count)
    {
        return startOfMonth()->subMonths($count);
    }


    /**
     * Get list of month year for the given limit.
     *
     * @param $limit
     * @return array
     */
    function getListOfMonthYears($limit)
    {
        if ($limit <= 0) {
            return [];
        }

        $monthYear = [];
        foreach (range(0, $limit - 1) as $value) { //  Carbon purpose subtracting the 1 from the limit.
            $monthYear[] = now()->subMonths($value)->format('m-Y');
        }

        return $monthYear;
    }


    /**
     * Capturing the payment amount.
     *
     * @param $razorpayPaymentId
     * @param $amount
     * @return array|mixed
     * @throws GuzzleException
     */
    function paymentCaptureRazorpayApi($razorpayPaymentId, $amount)
    {
        try {
            $razorpayPaymentKey = getRazorpayPaymentKey();
            $razorpayPaymentKeyId = $razorpayPaymentKey['razorpay_payment_key_id'];
            $razorpayPaymentKeySecret = $razorpayPaymentKey['razorpay_payment_key_secret'];
            $url = 'https://' . $razorpayPaymentKeyId . ':' . $razorpayPaymentKeySecret . '@api.razorpay.com/v1/payments/' . $razorpayPaymentId . '/capture';

            $client = new Client();
            $response = $client->post($url, ['json' => ['amount' => $amount]]);
            return json_decode($response->getBody(), true) ?? null;
        } catch (Exception $exception) {
            logError($exception, 'Error while calling razorpay payment api!', 'helpers@paymentCaptureRazorpayApi', ['input' => [$razorpayPaymentId, $amount]]);
            return null;
        }
    }

    /**
     * Transfer payment after capture.
     *
     * @param $razorpayPaymentId
     * @param $amount
     * @param $notes
     * @return array|mixed
     */
    function transferCapturedRazorpayApi($razorpayPaymentId, $amount, $notes)
    {
        try {
            $razorpayPaymentKey = getRazorpayPaymentKey();
            $razorpayPaymentKeyId = $razorpayPaymentKey['razorpay_payment_key_id'];
            $razorpayPaymentKeySecret = $razorpayPaymentKey['razorpay_payment_key_secret'];
            $url = 'https://' . $razorpayPaymentKeyId . ':' . $razorpayPaymentKeySecret . '@api.razorpay.com/v1/payments/' . $razorpayPaymentId . '/transfers';
            $data['transfers'][] = [
                'customer' => $notes['customer_id'],
                'amount' => $amount,
                'currency' => 'INR',
                'notes' => $notes,
            ];

            $client = new Client();
            $response = $client->post($url, ['json' => $data]);
            return json_decode($response->getBody(), true) ?? null;
        } catch (Exception $exception) {
            logError($exception, 'Error while calling razorpay payment api!', 'helpers@transferCapturedRazorpayApi', ['input' => [$razorpayPaymentId, $amount, $data]]);
            return null;
        }
    }

    /**
     * Check the input signature with hash_hmac() algorithm.
     *
     * @param $secretKey
     * @param $body
     * @param $inputSignature
     * @return bool
     */
    function checkSignatureFromRazorpay($secretKey, $body, $inputSignature)
    {
        $hashHmac = hash_hmac('sha256', json_encode($body), $secretKey);
        return hash_equals($hashHmac, $inputSignature);
    }

    /**
     * Cast the given time in minutes into HH:mm format.
     *
     * @param $valueInMinutes
     * @return string
     */
    function minutesToHMFormat($valueInMinutes)
    {
        $valueInMinutes = ($valueInMinutes > 0) ? $valueInMinutes : 0;
        $hours = floor($valueInMinutes / 60);
        $minutes = floor($valueInMinutes % 60);
        return sprintf("%02d", $hours) . ':' . sprintf("%02d", $minutes);
    }

    /**
     * Formats a number as a currency string.
     *
     * @param $value
     * @param bool $hyphenOnEmpty
     * @return string
     */
    function rupees_format($value, $hyphenOnEmpty = false)
    {
        if (isNullOrEmpty($value)) {
            return $hyphenOnEmpty ? '-' : '0.00';
        }

        return money_format('%!i', $value);
    }

    /**
     * jQuery Datatable sum amount function declaration helper.
     *
     * @return string
     */
    function sumAmountFunctionDeclaration()
    {
        return '
                jQuery.fn.dataTable.Api.register("sumAmount()", function () {
                    return this.flatten().reduce(function (a, b) {
                        if (typeof a === "string") {
                            a = a.replace(/[^\d.0]/g, "") * 1;
                        }
                        if (typeof b === "string") {
                            b = b.replace(/[^\d.0]/g, "") * 1;
                        }
                        return a + b;
                    }, 0);
                });
            ';
    }

    /**
     * jQuery Datatable sum amount function declaration helper.
     *
     * @return string
     */
    function sumAmountUnsignedFunctionDeclaration()
    {
        return '
                jQuery.fn.dataTable.Api.register("sumAmount()", function () {
                    return this.flatten().reduce(function (a, b) {
                        if (typeof a === "string") {
                            a = a.replace(/[,\/]/g, "") * 1;
                        }
                        if (typeof b === "string") {
                            b = b.replace(/[,\/]/g, "") * 1;
                        }
                        return a + b;
                    }, 0);
                });
            ';
    }

    /**
     * Find the average value
     * @param $inputArray
     * @return float|int
     */
    function findAverage($inputArray)
    {
        return array_sum($inputArray) / count($inputArray);
    }

    /**
     * @param $field
     * @return string
     */
    function checkTripType($field)
    {
        return $field == null ? '-' : ucfirst(str_replace('_', ' ', $field));
    }


    /**
     * Get the formatted mobile number
     * this logic is used in BookCabRequest
     *
     * @param $phone
     * @return false|string
     */
    function formatMobileNumber($phone)
    {
        // Remove the spaces from the given mobile number
        $phone = str_replace(' ', '', $phone);
        // Remove the hyphens from the given mobile number
        $phone = str_replace('-', '', $phone);
        // Remove the open bracket from the given mobile number
        $phone = str_replace('(', '', $phone);
        // Remove the close bracket from the given mobile number
        $phone = str_replace(')', '', $phone);
        // Remove the +91 from the mobile number
        return substr($phone, -10);
    }

    /**
     * Get list of Settings Permission from config file
     *
     * @param null $settingType
     * @param null $key
     *
     * @return array
     */
    function settingsPermissionConfig($settingType = null, $key = null)
    {
        $settingType = $settingType ? ('.' . $settingType) : '';
        $key = $key ? ('.' . $key) : '';
        return config('settings-permission' . $settingType . $key);
    }

    /**
     * Highlight the active menu in the sidebar.
     *
     * @param $path
     * @param string $active
     * @return string
     */
    function setActive($path, $active = 'active')
    {
        return call_user_func_array('Request::is', (array)$path) ? $active : '';
    }

    function displaywords($number)
    {
        info('sdinhs');
        $words = array('0' => '', '1' => 'ONE', '2' => 'TWO',
            '3' => 'THREE', '4' => 'FOUR', '5' => 'FIVE', '6' => 'SIX',
            '7' => 'SEVEN', '8' => 'EIGHT', '9' => 'NINE',
            '10' => 'TEN', '11' => 'ELEVEN', '12' => 'TWELVE',
            '13' => 'THIRTEEN', '14' => 'FOURTEEN',
            '15' => 'FIFTEEN', '16' => 'SIXTEEN', '17' => 'SEVENTEEN',
            '18' => 'EIGHTEEN', '19' => 'NINETEEN', '20' => 'TWENTY',
            '30' => 'THIRTY', '40' => 'FORTY', '50' => 'FIFTY',
            '60' => 'SIXTY', '70' => 'SEVENTY',
            '80' => 'EIGHTY', '90' => 'NINETY');
        $digits = array('', '', 'HUNDRED', 'THOUSAND', 'LAKH', 'CRORE');

        $number = explode(".", $number);
        $result = array("", "");
        $j = 0;
        foreach ($number as $val) {
            // loop each part of number, right and left of dot
            for ($i = 0; $i < strlen($val); $i++) {
                // look at each part of the number separately  [1] [5] [4] [2]  and  [5] [8]

                $numberpart = str_pad($val[$i], strlen($val) - $i, "0", STR_PAD_RIGHT); // make 1 => 1000, 5 => 500, 4 => 40 etc.
                if ($numberpart <= 20) { // if it's below 20 the number should be one word
                    $numberpart = 1 * substr($val, $i, 2); // use two digits as the word
                    $i++; // increment i since we used two digits
                    $result[$j] .= $words[$numberpart] . " ";
                } else {
                    //echo $numberpart . "<br>\n"; //debug
                    if ($numberpart > 90) {  // more than 90 and it needs a $digit.
                        $result[$j] .= $words[$val[$i]] . " " . $digits[strlen($numberpart) - 1] . " ";
                    } else if ($numberpart != 0) { // don't print zero
                        $result[$j] .= $words[str_pad($val[$i], strlen($val) - $i, "0", STR_PAD_RIGHT)] . " ";
                    }
                }
            }
            $j++;
        }
        if (trim($result[0]) != "") return $result[0] . "RUPEES ";
        if ($result[1] != "") return $result[1] . "PAISE";
        return " ONLY";
    }

    /**
     * @param $file
     * @return string
     * @throws RandomException
     */
    function generateUniqueFileName($file): string
    {
        return pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) .
            '_' . now()->timestamp . '_' . random_int(1000, 9999) .
            '.' . $file->getClientOriginalExtension();
    }

/**
 * @param $file_path
 * @return Application|string|UrlGenerator
 */
function generate_file_url($file_path): Application|string|UrlGenerator
    {
        if ($file_path && Storage::disk('public')->exists($file_path)) {
            return url(Storage::url($file_path));
        }

        $extension = pathinfo($file_path, PATHINFO_EXTENSION) ?? 'png';

        $dummyFiles = [
            'jpg' => 'images/dummy.jpg',
            'jpeg' => 'images/dummy.jpg',
            'png' => 'images/dummy.png',
            'gif' => 'images/dummy.png',
            'pdf' => 'files/dummy.pdf',
            'doc' => 'files/dummy.doc',
            'docx' => 'files/dummy.docx',
            'xls' => 'files/dummy.xls',
            'xlsx' => 'files/dummy.xlsx',
            'txt' => 'files/dummy.txt',
            'mp4' => 'videos/dummy.mp4',
            'mp3' => 'audio/dummy.mp3',
        ];

        $dummyFile = $dummyFiles[$extension] ?? 'images/dummy.png';

        return url(Storage::url($dummyFile));
    }

    /**
     * @throws JsonException
     * @throws ConnectionException
     */
    function generateFirebaseAccessToken(#[SensitiveParameter] $serviceAccountPath)
    {
        $serviceAccount = json_decode(file_get_contents($serviceAccountPath), true, 512, JSON_THROW_ON_ERROR);

        $now = time();
        $expires = $now + 3600;
        $jwtHeader = base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
        $jwtClaimSet = base64UrlEncode(json_encode([
            'iss' => $serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $expires,
            'iat' => $now,
        ], JSON_THROW_ON_ERROR));

        $unsignedJwt = $jwtHeader . '.' . $jwtClaimSet;
        $signature = '';
        openssl_sign($unsignedJwt, $signature, $serviceAccount['private_key'], 'sha256');
        $signedJwt = $unsignedJwt . '.' . base64UrlEncode($signature);

        // Exchange JWT for access token
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $signedJwt,
        ]);

        if ($response->successful()) {
            return $response->json()['access_token'];
        }

        return null;
    }

    /**
     * @param $data
     * @return string
     */
    function base64UrlEncode($data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * @param $query
     * @param Request $request
     * @param array $searchColumns
     * @return mixed
     */
    function dataFilter($query, Request $request, array $searchColumns = []): mixed
    {
        // Get only non-empty request inputs
        $inputs = collect($request->all())->filter();

        // Search filter
        $query->when($inputs->has('search') && !empty($searchColumns), function ($q) use ($inputs, $searchColumns) {
            $search = $inputs->get('search');
            $q->where(function ($subQuery) use ($searchColumns, $search) {
                foreach ($searchColumns as $column) {
                    $subQuery->orWhere($column, 'like', "%{$search}%");
                }
            });
        });

        // Date filter
        $query->when($inputs->has('created_from') && $inputs->has('created_to'), function ($q) use ($inputs) {
            $q->whereBetween('created_at', [
                Carbon::parse($inputs->get('created_from'))->startOfDay(),
                Carbon::parse($inputs->get('created_to'))->endOfDay()
            ]);
        });

        // Sorting
        $sortBy = $inputs->get('sort_by', 'created_at');
        $sortOrder = $inputs->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $inputs->get('per_page', 10);
        $page = $inputs->get('page', 1);

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Format paginated data into an array.
     *
     * @param $query
     * @return array
     */
    function dataFormatter($query): array
    {
        return [
            'current_page' => $query->currentPage(),
            'data' => $query->items(),
            'total' => $query->total(),
            'per_page' => $query->perPage(),
            'last_page' => $query->lastPage(),
        ];
    }
