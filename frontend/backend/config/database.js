const mysql = require('mysql2');
require('dotenv').config();

const connection = mysql.createConnection({
    host: process.env.DB_HOST || 'localhost',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'ev_data_marketplace'
});

connection.connect((err) => {
    if (err) {
        console.error('Lỗi kết nối database:', err);
        return;
    }
    console.log('Kết nối MySQL thành công');
});

module.exports = connection;