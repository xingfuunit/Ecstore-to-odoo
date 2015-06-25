#!/bin/sh

#需要修改以下三行数据 sphinx的安装目录、ecstore的安装目录、log的存放目录
sphinx_path="/alidata/server/sphinx/bin"
ecstore_path="/alidata/www/phpwind/ecstore"
log_path="/alidata/www/phpwind/ecstore/public/log"


"${sphinx_path}/searchd" --stop
"${sphinx_path}/indexer"  b2c_goods_delta --config "${ecstore_path}/app/search/config/sphinx.conf" --rotate
"${sphinx_path}/indexer" --merge b2c_goods_merge b2c_goods_delta --config "${ecstore_path}/app/search/config/sphinx.conf" >> "${log_path}/detal_goods_indexlog"
"${sphinx_path}/searchd"

