#!/bin/sh

# Kiểm tra gateway trước khi start service
until curl -s http://gateway >/dev/null; do
  echo "→ Waiting for gateway to be available..."
  sleep 3
done

echo "✓ Gateway is online. Starting auth-service..."

# Chạy Apache/PHP-FPM… (tùy service của bạn)
apache2-foreground &

# Giám sát gateway trong khi chạy
while true; do
  if ! curl -s http://gateway >/dev/null; then
    echo "✗ Gateway offline → stopping auth-service"
    killall apache2
    exit 1
  fi
  sleep 5
done
