job "pandora" {
  type = "service"

  group "pandora" {
    network {
      port "http" { }
    }

    service {
      name     = "pandora"
      port     = "http"
      provider = "nomad"
      tags = [
        "traefik.enable=true",
        "traefik.http.routers.pandora.rule=Host(`bokning.datasektionen.se`)",
        "traefik.http.routers.pandora.tls.certresolver=default",
      ]
    }

    task "pandora" {
      driver = "docker"

      config {
        image = var.image_tag
        ports = ["http"]
      }

      template {
        data        = <<ENV
{{ with nomadVar "nomad/jobs/pandora" }}
DATABASE_URL=postgres://pandora:{{ .db_password }}@postgres.dsekt.internal:5432/pandora
APP_KEY={{ .app_key }}
LOGIN_API_KEY={{ .login_api_key }}
SPAM_API_KEY={{ .spam_api_key }}
{{ end }}
PORT={{ env "NOMAD_PORT_http" }}
APP_DEBUG=false
APP_ENV=production
DB_CONNECTION=pgsql
LOGIN_API_URL=https://login.datasektionen.se
LOGIN_FRONTEND_URL=https://login.datasektionen.se
PLS_API_URL=https://pls.datasektionen.se/api
SPAM_API_URL=https://spam.datasektionen.se/api/sendmail
ENV
        destination = "local/.env"
        env         = true
      }

      resources {
        memory = 120
      }
    }
  }
}

variable "image_tag" {
  type = string
  default = "ghcr.io/datasektionen/pandora:latest"
}
