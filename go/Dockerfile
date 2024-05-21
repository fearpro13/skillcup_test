FROM golang:1.22

WORKDIR /server-go

COPY cmd cmd
COPY go.* ./

RUN cd cmd/server-go && go build -o /server-go/build/server

ENTRYPOINT ["/server-go/build/server"]