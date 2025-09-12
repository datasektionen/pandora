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
SSO_CLIENT_SECRET={{ .sso_client_secret }}
{{ end }}
PORT={{ env "NOMAD_PORT_http" }}
APP_URL=https://bokning.datasektionen.se
APP_DEBUG=false
APP_ENV=production
DB_CONNECTION=pgsql
SSO_PROVIDER=https://sso.datasektionen.se/op
SSO_CLIENT_ID=pandora
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
