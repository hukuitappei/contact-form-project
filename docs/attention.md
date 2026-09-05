# 注意点メモ

## Sailのdockerビルドが極端に遅い/止まる場合（PHP8.2ランタイム再ビルド時）

`./vendor/bin/sail build` で `apt-get update && apt-get upgrade -y && apt-get install ...` の
ステップが何十分経っても終わらない、または`Get:`の番号が後戻りするように見える場合。

**原因**: このネットワーク（ISP/経路）では、`archive.ubuntu.com` / `security.ubuntu.com` への
平文HTTP(80番)通信がブラックホール化する（TCP接続は成立するが応答が0バイトのままタイムアウトする）。
HTTPS(443番)や他のホスト（deb.debian.org、apt.postgresql.org等）は正常に通る。

**適用した対処（ローカルのみ・git管理外）**:
`vendor/laravel/sail/runtimes/8.2/Dockerfile` に以下を追加し、aptのソースをHTTPS化した。

```dockerfile
COPY ca-certificates.crt /etc/ssl/certs/ca-certificates.crt
RUN sed -i \
    -e 's|http://archive.ubuntu.com|https://archive.ubuntu.com|' \
    -e 's|http://security.ubuntu.com|https://security.ubuntu.com|' \
    /etc/apt/sources.list.d/ubuntu.sources
```

`ca-certificates.crt`はホスト側の `/etc/ssl/certs/ca-certificates.crt` をコピーしたもの
（素の`ubuntu:24.04`イメージには証明書ストアが入っておらず、HTTPS化しただけだと
証明書検証エラーで失敗するため）。

**重要**: `vendor/`はgit管理外なので、この修正はリポジトリに含まれない。
`composer install`のやり直しや別マシン/採点環境での`vendor`再生成時に同じ症状が出た場合は、
上記の手順を再度手動で適用する必要がある（同じ症状が出ない環境では対応不要）。
