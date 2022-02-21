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

        stage('Prepare Environments') {
            openshift.withCluster() {
                openshift.withProject(params.namespace) {
                    parallel {
                        stage("Build environment") {
                            steps {
                                script {
                                    openshift.apply(readFile("build/imagestream.yaml"))
                                    openshift.apply(readFile("build/buildconfig.yaml"))
                                }
                            }
                        }

                        stage("Deploy environment") {
                            steps {
                                script {
                                    openshift.apply(readFile("deploy/deployment.yaml"))
                                    openshift.apply(readFile("deploy/service.yaml"))
                                    openshift.apply(readFile("deploy/route.yaml"))
                                }
                            }
                        }

                        stage("Install dependencies") {
                            steps {
                                script {
                                    sh 'composer install'
                                }
                            }
                        }
                    }
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

        stage('Deploy application') {
            steps {
                script {
                    openshift.withCluster() {
                        openshift.withProject(params.namespace) {
                            def istag = openshift.selector("imagestreamtag", "php-todo-app:latest")
                            def imageReference = istag.object().image.dockerImageReference

                            def deployment = openshift.selector("deployment", "php-todo-app").object()
                            deployment.spec.template.spec.containers[0].image = imageReference
                            deployment.spec.paused = 'false'
                            openshift.apply(deployment)
                        }
                    }
                }
            }
        }
    }

}