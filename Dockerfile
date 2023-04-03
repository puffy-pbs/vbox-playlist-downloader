FROM php:8.0-cli

WORKDIR /app

RUN apt-get update && apt-get install ffmpeg -y

COPY . .

CMD ["php", "example.php"]
