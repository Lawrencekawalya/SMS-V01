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
    }
}
