# docker build -t awc737/blog .
# docker run --name blog -it -p 8001:80 -v `pwd`:/var/www/blog awc737/blog
# docker rm blog

FROM node:5.1
ENV NODE_ENV production
RUN npm install -g harp
#VOLUME /var/www/blog
WORKDIR /var/www/blog
EXPOSE 80
CMD harp server /var/www/blog --port 80
