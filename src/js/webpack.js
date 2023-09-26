// 讀取所有同副檔名的檔案
function requireAll(read) { read.keys().forEach(read); };

requireAll(require.context('../', true, /\.php$/i));
requireAll(require.context('../', true, /\.html$/i));
requireAll(require.context('../img/', true, /\.(png|jpe?g|gif|svg|webp)$/i));

import '../css/webpack.css';