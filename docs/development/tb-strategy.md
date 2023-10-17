# Strategy of the Technical Board - July 2023


## Introduction

As the [Technical Board of the ILIAS Society](https://docu.ilias.de/goto_docu_cat_12438.html)
we are tasked to devise strategies that guide the development of ILIAS. These
strategies are based on [the ILIAS Manifest](https://docu.ilias.de/goto_docu_copa_9682.html)
and [the ILIAS Vision](https://docu.ilias.de/goto_docu_copa_9683.html), which were
both adopted by the general assembly of the ILIAS Society.

The last two years have been humbling and require some adjustments of our ambitions
and of the perception of our current status. The project to support PHP 8 doubled
the time for the release cycle of ILIAS 8. Shortfalls in our resources as well as
problems in the code base have become obvious. Ever increasing requirements and
awareness for security and the steady speed of the development in PHP and the web
ecosystem put an additional strain on the development process. Also, the general
insight that ILIAS can not just grow to do even more is taking root in the community.
Many additions and adjustments in this strategy are informed by this insight.

But there also is a lot of positive development and progress in our community. Many
goals which we have set in the previous strategy have been achieved by now and thus
have been removed from this paper. New forms of cooperation have been established
and contributions to all parts of our software and community have grown. This puts
us in an excellent position to tackle the challenges ahead. We are
proud to be part of such a great community.


## Strategic Goals

### Community Supported

As a [Free/Libre and Open Source Software (FLOSS)](https://www.gnu.org/philosophy/floss-and-foss.en.html),
ILIAS has been carried by a strong and active community for many years. The whole
process of developing ILIAS towards our vision and of implementing our strategies
is based on this community and goes far beyond writing code. **Everyone in the
community should be able to contribute to this great application according to their
individual strengths.**

We set the following goals:

* Extraordinary contributions and efforts from our community members are show-cased
  and honored in an appropriate way.
* Since requirements have increased for all parts of ILIAS, the number of maintainers,
  test-case-authors, testers, and contributors of any kind grows proportionally.
  The resources provided by these people are used efficiently according to the skills
  of the individuals.
* The ILIAS Society ensures that established tools are used and maintained. Working
  with the tools becomes more effective and and efficient.
* The concepts and terminology used in the application are shared by users, learning
  specialists, and developers. They are fully documented and consistently used in
  code, in READMEs, in the feature wiki and all other texts and publications. This
  is a prerequisite to understand if and how ILIAS can be used, configured, and
  extended.
* Discussions in our community are welcome and are always based on our core values
  and factual arguments. We minimize losses in productivity and motivation, caused
  by interpersonal disagreements.


### Reliable Learning Management

The salience of data security and privacy in the political and social discourse is
increasing. Organizations heavily rely on IT tools for their daily work. The accuracy
and availability of central services is crucial. ILIAS is used in environments where
the generated data is used when making high stakes decisions. Requirements on ILIAS
thus have increased too. **People and organisations should be able to rely on ILIAS'
qualities regarding security, privacy, and availability.**

We set the following goals:

* The methods to assess and increase code quality, that we have developed for and
  during the PHP 8 project, will be employed regularly in our development process.
  They are continously strengthend and enhanced to steadily improve the quality of
  our existing code and assess new code.
* We already have various automatisms and approaches that individual developers
  can use to improve their contributions to ILIAS. These tools are used naturally
  throughout our development process and thereby brought to full fruition.
* The accessibility for automatism (probably: automation) of our code base and
  processes is steadily increased. Automatisms are used or added to our procedures
  when their overall impact on our resources is positive.
* The insights and methods that we use to implement secure software are strongly
  supported by the services in our software. Every interface makes it simple to do
  the right and secure thing while making it hard to degrade the security of the
  system.
* Our approach to process security issues is improved. Short term mitigations are
  published even faster, while the root causes for the issues are identified and
  mid or long term efforts to remove them are devised and tracked reliably. Security
  considerations and issues have a high priority in all development activities.
* We only include dependencies from other projects if they fit our requirements
  regarding security and general reliability. Dependencies are checked for each
  release and vetted thoroughly.
* Privacy issues and requirements are supported in a similar manner as security and
  accessibility issues and requirements. A strong framework of technical and procedural
  mechanisms to track and find personal data exists, that allows administrators to
  understand the data that ILIAS stores.


### Usable for Everyone

ILIAS is used all around the world in various languages by universities, institutions
of public service, companies and schools of various sizes. Among the users are many
individuals with special needs, digital immigrants as well as experts in e-learning.
**ILIAS users can already rely on existing measures to receive a consistent user
experience in the whole system.**

We are proud and thankful that expert groups provide their skills for the user interface
design with regards to simplicity, effectiveness, design for error and visibility as
well as to guarantee a good accessibility accompanied with a consistent and diverse
language to meet the requirements of an increasingly more diverse target group.

The UI framework has been established as a tool for developers to create a predictable
user interface with special regards to accessbility throughout all ILIAS components.
As the Technical Board we are happy to see more and more legacy elements being replaced
by the framework equivalents. The inventory of the UI components grows through large
community projects.

We do not set new goals here. We instead ask everyone to make sure that we stay on the
awesome trajectory we are currently on.


### Adaptable Learning Environment

ILIAS targets a huge variety of institutions with different sizes, diverse didactic
requirements, and a wide range of use cases. It needs to be adaptable to the different
requirements arising under these circumstances without compromising other key factors
of quality software, e.g. ease of use and maintainability. A growing user base with
different backgrounds increases the importance of this aim, but makes it also harder
to achieve it.

We set the following goals:

* The design of interfaces between different components is an integral part of our
engineering efforts. Interfaces are well structured and follow the interface segregation
principle. All services contain a README with thorough documentation of their purpose
and on how they can be used providing or pointing to examples of their usage.
* An open process is established which enables the integration of components based
on quality criteria. This process is supported by technical measures and checks, as
well as by audits of professionals. Adding components to and removing them from ILIAS
is equally simple. As the ILIAS platform evolves, compatibility and upgrade paths
across releases are ensured by appropriate upgrade processes and deprecation markings.
* Mechanism that allow ILIAS to be easily adapted to the diverse situation found in
its user base and beyond are implemented and documented. Developers and users can
easily swap, mix, and match parts of ILIAS. ILIAS provides a consolidated, reliable,
and sustainable component system to create feature rich applications.


### Platform for Learning

Modern devices are connected to an ubiquitous network of computers. Modern software
needs to support that mode of operation. We thus cannot just think of ILIAS as a
stand-alone software but instead need to understand it as a small part of a huge
system of connected components. The environment for our development effort is similar.
We are not alone but depend on other projects and people. We want ILIAS and its
community to collaborate with other organisations and systems, to share work and
learn from each other.

We set the following goals:

* ILIAS provides a well-defined API that makes it easy to connect ILIAS to other
  systems on the front- and backend.
* We diligently select the standards and interfaces we want to implement in ILIAS.
  When doing so, we also seek to understand the implications and opportunities for
  our system and implement according adjustments.
* We participate in efforts to define standards and contribute to the general ecosystem.
  The free/libre and open source communities and learning communities can and should
  benefit from our knowledge and code, just as we benefit from theirs. We shall
  cooperate closely with communities that share our values and challenges.



## Usage of this Document

Just as with the strategies before, we, the Technical Board, will use this paper
to derive consistent measures and argumentations in concrete situations. Thus, by
knowing and understanding this strategy paper, people should be able to understand
and anticipate our actions at least approximately. Transparency is important for
effective leadership.

We hope that everyone involved in this community will feel inclined to adopt these
goals and act accordingly. Use this paper in decision making, discussions, when
setting priorities and whenever you see fit. We are so much stronger when we pull
in the same direction.
