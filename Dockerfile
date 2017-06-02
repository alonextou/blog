# sudo docker build -t awc737/blog .
# sudo docker run -it -p 80:80 -v /home/alex/dev/web/blog:/var/www/blog awc737/blog

FROM node:5.1
#ENV DOCKER_BUILD docker build -t dockerimages/blog_dspeed git://github.com/dockerimages/harp-git
#ENV DOCKER_RUN docker run -d --restart=always --name=alexcrawford-blog-data -v /home/alex/dev/web/blog:/var/www/blog dockerimages/docker-harp
ENV NODE_ENV production
RUN npm install -g harp
#VOLUME /var/www/blog
WORKDIR /var/www/blog
EXPOSE 80
CMD harp server /var/www/blog --port 80
