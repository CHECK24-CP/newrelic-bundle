job("Build") {
    startOn {
        gitPush {
            enabled = true
        }
    }

    container(image = "check24-cp.registry.jetbrains.space/p/dev-ops/central-docker/devops-php-build:8.2-cli") {
        env["COMPOSER_AUTH"] = "{{ project:COMPOSER_AUTH }}"

        shellScript {
            content = """
                set -e

                ## Install dependencies
                composer install --no-progress --no-interaction || true

                composer lint
                composer test
                composer audit
            """.trimIndent()
        }
    }
}
