<?php
// Định nghĩa nhiều API keys (thay bằng keys thực của bạn, phân cách bằng dấu phẩy hoặc mảng)
// Ví dụ: Các keys từ Google AI Studio (tạo nhiều account để có nhiều keys miễn phí)
$rawKeys = [
    'AIzaSyBk7twdv6n450gZtjhbNN_ugriuqkut-UE',
    'AIzaSyCCcTfhbAFLpKHWBbmQj4gz2VzYMTXy4J8',  // Key 1
    'AIzaSyC53rYBDAMuxqV0h954Zag_Ea1BH494Lrg',  // Key 2
];

// Lọc keys hợp lệ (không rỗng và không phải 'xxx' placeholder)
$validKeys = array_filter($rawKeys, function($key) {
    return !empty($key) && $key !== 'xxx';
});

define('GEMINI_API_KEYS', $validKeys);  // Mảng keys đã lọc
?>