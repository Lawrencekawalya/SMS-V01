<?php

namespace Database\Seeders;

use App\Models\CourseUnit;
use App\Models\Department;
use Illuminate\Database\Seeder;

class CourseUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cs = Department::where('code', 'CS')->first();
        $it = Department::where('code', 'IT')->first();
        $af = Department::where('code', 'AF')->first();

        $courses = [
            // Computer Science
            [
                'dept' => $cs,
                'code' => 'CSC1101',
                'name' => 'Introduction to Computer Science',
                'credit_units' => 4.0,
                'description' => 'Fundamental concepts of computing, discrete mathematics, digital logic, and algorithmic thinking.',
                'status' => 'active',
            ],
            [
                'dept' => $cs,
                'code' => 'CSC1102',
                'name' => 'Structured Programming in C',
                'credit_units' => 4.0,
                'description' => 'Procedural programming syntax, data types, control flow, functions, arrays, pointers, and memory allocation.',
                'status' => 'active',
            ],
            [
                'dept' => $cs,
                'code' => 'MTH1102',
                'name' => 'Calculus & Analytical Geometry',
                'credit_units' => 3.0,
                'description' => 'Limits, continuity, differentiation, integration, series, and multi-variable calculus for engineering applications.',
                'status' => 'active',
            ],
            [
                'dept' => $cs,
                'code' => 'CSC1201',
                'name' => 'Object Oriented Programming with Java',
                'credit_units' => 4.0,
                'description' => 'Object-oriented paradigm, encapsulation, inheritance, polymorphism, abstract classes, interfaces, and exception handling.',
                'status' => 'active',
            ],
            [
                'dept' => $cs,
                'code' => 'CSC1202',
                'name' => 'Data Structures and Algorithms',
                'credit_units' => 4.0,
                'description' => 'Stacks, queues, linked lists, trees, graphs, sorting, searching, and algorithmic asymptotic complexity analysis.',
                'status' => 'active',
            ],
            [
                'dept' => $cs,
                'code' => 'CSC2101',
                'name' => 'Database Management Systems',
                'credit_units' => 4.0,
                'description' => 'Relational database theory, SQL DDL/DML, normalization, indexing, transaction processing, and ACID guarantees.',
                'status' => 'active',
            ],
            [
                'dept' => $cs,
                'code' => 'CSC2102',
                'name' => 'Operating Systems Concepts',
                'credit_units' => 4.0,
                'description' => 'Kernel architecture, process scheduling, concurrency, deadlocks, virtual memory management, and file systems.',
                'status' => 'active',
            ],
            [
                'dept' => $cs,
                'code' => 'CSC3101',
                'name' => 'Software Engineering Principles',
                'credit_units' => 4.0,
                'description' => 'Software lifecycle methodologies (Agile, Scrum), UML modeling, architectural patterns, CI/CD, and quality assurance.',
                'status' => 'active',
            ],
            [
                'dept' => $cs,
                'code' => 'CSC3201',
                'name' => 'Artificial Intelligence & Machine Learning',
                'credit_units' => 4.0,
                'description' => 'Search algorithms, supervised/unsupervised machine learning, neural networks, natural language processing, and ethical AI.',
                'status' => 'active',
            ],

            // Information Technology
            [
                'dept' => $it,
                'code' => 'BIT1101',
                'name' => 'Foundations of Information Technology',
                'credit_units' => 3.0,
                'description' => 'Overview of IT hardware, operating systems, enterprise software, and societal impacts of computing.',
                'status' => 'active',
            ],
            [
                'dept' => $it,
                'code' => 'BIT1102',
                'name' => 'Computer Architecture and Organization',
                'credit_units' => 3.0,
                'description' => 'CPU design, instruction sets, pipelining, cache memory hierarchy, bus interfaces, and I/O subsystems.',
                'status' => 'active',
            ],
            [
                'dept' => $it,
                'code' => 'BIT1201',
                'name' => 'Data Communications and Computer Networks',
                'credit_units' => 4.0,
                'description' => 'OSI and TCP/IP reference models, routing protocols, switching, IP subnetting, DNS, and network packet analysis.',
                'status' => 'active',
            ],
            [
                'dept' => $it,
                'code' => 'BIT2101',
                'name' => 'Web Technologies and Development',
                'credit_units' => 4.0,
                'description' => 'Full-stack web architecture: modern HTML5, responsive CSS, JavaScript, RESTful APIs, and backend frameworks.',
                'status' => 'active',
            ],
            [
                'dept' => $it,
                'code' => 'BIT2201',
                'name' => 'Cloud Computing and Virtualization',
                'credit_units' => 3.0,
                'description' => 'Virtual machines, containerization (Docker, Kubernetes), public cloud architectures (AWS, Azure), and serverless computing.',
                'status' => 'active',
            ],
            [
                'dept' => $it,
                'code' => 'BIT3101',
                'name' => 'Information Systems Security and Audit',
                'credit_units' => 3.0,
                'description' => 'Threat modeling, cryptography, vulnerability scanning, penetration testing basics, compliance frameworks, and incident response.',
                'status' => 'active',
            ],

            // Diploma in Information Technology (DIT) - 28 Courses
            // Year 1, Semester 1 (27 Credit Units)
            [
                'dept' => $it,
                'code' => 'DIT1101',
                'name' => 'Entrepreneurial Skill Development',
                'credit_units' => 4.0,
                'description' => 'Principles of entrepreneurship, business opportunity identification, feasibility analysis, budgeting, and small business management.',
                'status' => 'active',
            ],
            [
                'dept' => $it,
                'code' => 'DIT1102',
                'name' => 'Computer Fundamentals',
                'credit_units' => 4.0,
                'description' => 'Introduction to computer hardware, basic operating principles, peripheral devices, software applications, and basic troubleshooting.',
                'status' => 'active',
            ],
            [
                'dept' => $it,
                'code' => 'DIT1103',
                'name' => 'Programming Fundamentals',
                'credit_units' => 4.0,
                'description' => 'Core principles of programming, algorithm design, pseudocode, flowcharting, control structures, and fundamental coding techniques.',
                'status' => 'active',
            ],
            [
                'dept' => $it,
                'code' => 'DIT1104',
                'name' => 'Computer Architecture and Organization',
                'credit_units' => 3.0,
                'description' => 'Digital logic circuits, CPU architecture, memory hierarchy, input/output systems, instruction set architecture, and assembly concepts.',
                'status' => 'active',
            ],
            [
                'dept' => $it,
                'code' => 'DIT1105',
                'name' => 'Mathematics for IT',
                'credit_units' => 3.0,
                'description' => 'Discrete mathematics, propositional logic, sets, relations, functions, matrices, and probability theory for computing systems.',
                'status' => 'active',
            ],
            [
                'dept' => $it,
                'code' => 'DIT1106',
                'name' => 'Teleconferencing Technologies',
                'credit_units' => 3.0,
                'description' => 'Principles of video conferencing, VoIP systems, collaborative communication tools, bandwidth requirements, and remote meeting setups.',
                'status' => 'active',
            ],
            [
                'dept' => $it,
                'code' => 'DIT1107',
                'name' => 'Communication Skills',
                'credit_units' => 3.0,
                'description' => 'Professional and academic communication, technical report writing, presentation techniques, public speaking, and interpersonal dynamics.',
                'status' => 'active',
            ],
            [
                'dept' => $it,
                'code' => 'DIT1108',
                'name' => 'Understanding The Bible',
                'credit_units' => 3.0,
                'description' => 'Exploration of biblical history, themes, literary genres, spiritual growth, and ethical application of biblical teachings.',
                'status' => 'active',
            ],

            // Year 1, Semester 2 (25 Credit Units)
            [
                'dept' => $it,
                'code' => 'DIT1201',
                'name' => 'Introduction to Statistics',
                'credit_units' => 3.0,
                'description' => 'Descriptive and inferential statistics, data representation, probability distributions, sampling techniques, and hypothesis testing.',
                'status' => 'active',
            ],
            [
                'dept' => $it,
                'code' => 'DIT1202',
                'name' => 'Structured Programming (C++)',
                'credit_units' => 4.0,
                'description' => 'Procedural and modular programming using C++, pointers, file handling, memory management, and introductory object orientation.',
                'status' => 'active',
            ],
            [
                'dept' => $it,
                'code' => 'DIT1203',
                'name' => 'Principles of Operating Systems',
                'credit_units' => 4.0,
                'description' => 'Operating system structures, process scheduling, concurrency, deadlocks, virtual memory management, and file system architectures.',
                'status' => 'active',
            ],
            [
                'dept' => $it,
                'code' => 'DIT1204',
                'name' => 'Database Fundamentals',
                'credit_units' => 4.0,
                'description' => 'Relational database concepts, entity-relationship modeling, relational algebra, SQL querying, and database normalisation.',
                'status' => 'active',
            ],
            [
                'dept' => $it,
                'code' => 'DIT1205',
                'name' => 'Computer Networks',
                'credit_units' => 4.0,
                'description' => 'Network topologies, OSI and TCP/IP models, Ethernet, IP addressing and subnetting, network hardware configuration, and routing basics.',
                'status' => 'active',
            ],
            [
                'dept' => $it,
                'code' => 'DIT1206',
                'name' => 'Practicum',
                'credit_units' => 3.0,
                'description' => 'Hands-on practical placement, industry exposure, workplace problem-solving, and technical reporting in an IT environment.',
                'status' => 'active',
            ],
            [
                'dept' => $it,
                'code' => 'DIT1207',
                'name' => 'Christian Ethics',
                'credit_units' => 3.0,
                'description' => 'Christian moral principles, ethical decision making, personal integrity, professional ethics, and social responsibility.',
                'status' => 'active',
            ],

            // Year 2, Semester 1 (23 Credit Units)
            [
                'dept' => $it,
                'code' => 'DIT2101',
                'name' => 'Systems Analysis & Design',
                'credit_units' => 3.0,
                'description' => 'System development life cycle (SDLC), requirements gathering, data modeling, UML diagrams, prototyping, and user interface design.',
                'status' => 'active',
            ],
            [
                'dept' => $it,
                'code' => 'DIT2102',
                'name' => 'Computer Support & Maintenance I',
                'credit_units' => 4.0,
                'description' => 'PC hardware troubleshooting, motherboard components, power supplies, BIOS/UEFI configuration, preventive maintenance, and basic diagnostics.',
                'status' => 'active',
            ],
            [
                'dept' => $it,
                'code' => 'DIT2103',
                'name' => 'Advanced Databases (Oracle, SQL Server)',
                'credit_units' => 4.0,
                'description' => 'Advanced SQL, stored procedures, triggers, PL/SQL, database security, backup and recovery, and enterprise database management using Oracle and SQL Server.',
                'status' => 'active',
            ],
            [
                'dept' => $it,
                'code' => 'DIT2104',
                'name' => 'Research Methods',
                'credit_units' => 3.0,
                'description' => 'Scientific research methodology, research design, qualitative and quantitative data collection, literature review, and academic writing.',
                'status' => 'active',
            ],
            [
                'dept' => $it,
                'code' => 'DIT2105',
                'name' => 'Multimedia Technologies',
                'credit_units' => 3.0,
                'description' => 'Digital audio, video, graphics processing, multimedia authoring tools, animation techniques, and web-based interactive media creation.',
                'status' => 'active',
            ],
            [
                'dept' => $it,
                'code' => 'DIT2106',
                'name' => 'Network Security',
                'credit_units' => 3.0,
                'description' => 'Network vulnerabilities, firewalls, intrusion detection systems (IDS/IPS), cryptographic protocols, VPNs, and defense-in-depth strategies.',
                'status' => 'active',
            ],
            [
                'dept' => $it,
                'code' => 'DIT2107',
                'name' => 'Computer Networks and Data Communication',
                'credit_units' => 3.0,
                'description' => 'Advanced networking protocols, signal transmission, WAN technologies, wireless networks, network switching, and performance tuning.',
                'status' => 'active',
            ],

            // Year 2, Semester 2 (16 Credit Units)
            [
                'dept' => $it,
                'code' => 'DIT2201',
                'name' => 'System Administration II',
                'credit_units' => 2.0,
                'description' => 'Enterprise server administration, user and group policy management, LDAP/Active Directory, automated scripting, and server hardening.',
                'status' => 'active',
            ],
            [
                'dept' => $it,
                'code' => 'DIT2202',
                'name' => 'Computer Support & Maintenance II',
                'credit_units' => 4.0,
                'description' => 'Advanced computer diagnostics, component repair, operating system troubleshooting, virus/malware eradication, and IT helpdesk management.',
                'status' => 'active',
            ],
            [
                'dept' => $it,
                'code' => 'DIT2203',
                'name' => 'Internet & Web Programming',
                'credit_units' => 2.0,
                'description' => 'Web application design, client-side scripting, dynamic web pages, RESTful APIs, and responsive mobile-friendly web development.',
                'status' => 'active',
            ],
            [
                'dept' => $it,
                'code' => 'DIT2204',
                'name' => 'Enterprise Networking',
                'credit_units' => 3.0,
                'description' => 'Enterprise network design, VLANs, trunking, inter-VLAN routing, OSPF, enterprise switching, and high-availability network infrastructures.',
                'status' => 'active',
            ],
            [
                'dept' => $it,
                'code' => 'DIT2205',
                'name' => 'Data Communications',
                'credit_units' => 3.0,
                'description' => 'Transmission media, digital and analog modulation, multiplexing, error detection and correction, and data link layer protocols.',
                'status' => 'active',
            ],
            [
                'dept' => $it,
                'code' => 'DIT2206',
                'name' => 'DIT Project',
                'credit_units' => 2.0,
                'description' => 'Capstone diploma project involving the conception, design, implementation, documentation, and oral defense of an IT software or systems solution.',
                'status' => 'active',
            ],

            // Accounting & Finance
            [
                'dept' => $af,
                'code' => 'AFN1101',
                'name' => 'Financial Accounting I',
                'credit_units' => 4.0,
                'description' => 'Double-entry bookkeeping, trial balance, preparation of financial statements, and IFRS fundamentals.',
                'status' => 'active',
            ],
            [
                'dept' => $af,
                'code' => 'AFN1102',
                'name' => 'Principles of Management & Organization',
                'credit_units' => 3.0,
                'description' => 'Management theories, leadership dynamics, strategic planning, human resource management, and organizational behavior.',
                'status' => 'active',
            ],
            [
                'dept' => $af,
                'code' => 'AFN1201',
                'name' => 'Microeconomics for Business Decisions',
                'credit_units' => 3.0,
                'description' => 'Supply and demand dynamics, consumer behavior, market structures, pricing strategies, and elasticity of demand.',
                'status' => 'active',
            ],
            [
                'dept' => $af,
                'code' => 'AFN2101',
                'name' => 'Managerial & Cost Accounting',
                'credit_units' => 4.0,
                'description' => 'Cost classification, job/process costing, variance analysis, budgeting, and managerial decision support.',
                'status' => 'active',
            ],
            [
                'dept' => $af,
                'code' => 'AFN2201',
                'name' => 'Corporate Finance and Valuation',
                'credit_units' => 4.0,
                'description' => 'Capital budgeting, weighted average cost of capital (WACC), dividend policy, working capital management, and company valuation.',
                'status' => 'active',
            ],
        ];

        foreach ($courses as $c) {
            if ($c['dept']) {
                CourseUnit::firstOrCreate(
                    ['code' => $c['code']],
                    [
                        'department_id' => $c['dept']->id,
                        'name' => $c['name'],
                        'credit_units' => $c['credit_units'],
                        'description' => $c['description'],
                        'status' => $c['status'],
                    ]
                );
            }
        }

        // Seed realistic course prerequisites
        $prerequisites = [
            'CSC1201' => ['CSC1102'], // OOP Java requires C Programming
            'CSC1202' => ['CSC1102'], // Data Structures requires C Programming
            'CSC2101' => ['BIT1101'], // Database Design requires Foundations of IT
            'CSC2102' => ['CSC1101'], // Operating Systems requires Intro to CS
            'CSC3101' => ['CSC1201', 'CSC2101'], // Cloud Systems requires OOP & DB
            'BIT2101' => ['BIT1101'], // Web Systems requires Foundations of IT
            'BIT2201' => ['CSC1201'], // Mobile Dev requires OOP Java

            // DIT Prerequisites
            'DIT1202' => ['DIT1103'], // Structured Programming (C++) requires Programming Fundamentals
            'DIT2102' => ['DIT1102'], // Computer Support & Maint I requires Computer Fundamentals
            'DIT2103' => ['DIT1204'], // Advanced Databases requires Database Fundamentals
            'DIT2106' => ['DIT1205'], // Network Security requires Computer Networks
            'DIT2107' => ['DIT1205'], // Computer Networks & Data Comm requires Computer Networks
            'DIT2202' => ['DIT2102'], // Computer Support & Maint II requires Computer Support & Maint I
            'DIT2204' => ['DIT1205'], // Enterprise Networking requires Computer Networks
            'DIT2206' => ['DIT2104'], // DIT Project requires Research Methods
        ];

        foreach ($prerequisites as $courseCode => $prereqCodes) {
            $course = CourseUnit::where('code', $courseCode)->first();
            if ($course) {
                $prereqIds = CourseUnit::whereIn('code', $prereqCodes)->pluck('id')->toArray();
                $course->prerequisites()->syncWithoutDetaching($prereqIds);
            }
        }
    }
}
