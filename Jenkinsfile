parameters {
    string(name: 'namespace', description: 'Ziel Project zum Deployen')
}

pipeline {

    agent {
        node {
            label 'php'
        }
    }

    options {
        timeout(time: 30, unit: 'MINUTES')
    }

    stages {
        stage('Checkout') {
            steps {
                script {
                    checkout scm
                }
            }
        }

        stage('Prepare Environment') {
            steps {
                script {
                    openshift.withCluster() {
                        openshift.withProject(params.namespace) {
                            openshift.apply(readFile("build/imagestream.yaml"))
                            openshift.apply(readFile("build/buildconfig.yaml"))
                        }
                    }
                }
            }
        }

        stage('Install Dependencies') {
            steps {
                script {
                    sh 'composer install'
                }
            }
        }

        stage('Build image') {
            steps {
                script {
                    openshift.withCluster() {
                        openshift.withProject(params.namespace) {
                            def bc = openshift.selector("buildconfig", "php-todo-app")
                            def build = bc.startBuild("--from-dir=.", "--wait")

                            build.logs('-f')
                        }
                    }
                }
            }
        }

        stage('Prepare Deployment') {
            steps {
                script {
                    openshift.withCluster() {
                        openshift.withProject(params.namespace) {
                            openshift.apply(readFile("deploy/deployment.yaml"))
                            openshift.apply(readFile("deploy/service.yaml"))
                            openshift.apply(readFile("deploy/route.yaml"))
                        }
                    }
                }
            }
        }

        stage('Deploy latest image') {
            steps {
                script {
                    openshift.withCluster() {
                        openshift.withProject(params.namespace) {
                            def istag = openshift.selector("imagestreamtag", "php-todo-app:latest")
                            def imageReference = istag.image.dockerImageReference
                            echo imageReference
                        }
                    }
                }
            }
        }
    }

}