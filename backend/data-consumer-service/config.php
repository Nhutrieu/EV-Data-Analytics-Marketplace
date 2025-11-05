<?php
/**
 * config.php
 * 
 * Lưu trữ các thông tin nhạy cảm PayOSS
 * KHÔNG commit lên git
 */

return [
    // Thông tin tài khoản PayOSS
    'PAYOSS_CLIENT_ID'   => '0a8c06f5-1861-4ede-ba7a-b8de9560534c',   // Điền Client ID của bạn
    'PAYOSS_API_KEY'     => '1391e0e3-f9a4-4f54-9e3c-6eae0b044585',     // Điền API Key của bạn
    'PAYOSS_CHECKSUM'    => '4fd99a4e99c0de621e3a11d69d54b447fc76bf8f61fef9ac0080432f1ef46c2e' // Điền Checksum Key (dùng validate webhook)

    // Nếu cần, bạn có thể thêm các config khác ở đây
];
