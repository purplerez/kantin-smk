#!/usr/bin/env bash
# Smoke test: login tiap role & buka semua halaman, laporkan status HTTP.
BASE=${1:-http://127.0.0.1:8090}
login() { # email password
  local jar=$(mktemp)
  local token=$(curl -s -c $jar $BASE/login | grep -oP 'name="_token" value="\K[^"]+')
  curl -s -b $jar -c $jar -o /dev/null -w "" -X POST $BASE/login -d "_token=$token&email=$1&password=$2"
  echo $jar
}
check() { # jar label path
  printf "%-14s %-28s %s\n" "$1" "$2" "$(curl -s -b $3 -o /tmp/page.html -w '%{http_code}' $BASE$2) $(grep -c 'Whoops\|ErrorException\|Exception' /tmp/page.html | sed 's/^0$//')"
}
J=$(login siswa@smkgo.id buyer123)
for p in / /tenant/kantin-bu-rina /keranjang /checkout /pesanan /akun /admin /tenant-panel; do check buyer $p $J; done
J=$(login staff@bu-rina.id staff123)
for p in /tenant-panel/pesanan /tenant-panel /tenant-panel/menu /; do check staff $p $J; done
J=$(login owner@bu-rina.id owner123)
for p in /tenant-panel /tenant-panel/pesanan /tenant-panel/menu /tenant-panel/pembayaran; do check owner $p $J; done
J=$(login admin@smkgo.id admin123)
for p in /admin /admin/transaksi /admin/tenant /admin/pengguna /admin/audit-log /; do check admin $p $J; done
