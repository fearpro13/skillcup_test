FROM golang:1.22

WORKDIR /app-go

COPY cmd cmd
COPY *.go ./
COPY go.* ./

RUN cd cmd/app-go && go build -o /app-go/build/server

ENTRYPOINT ["/app-go/build/server"]